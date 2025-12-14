<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "@/services/api";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
  CardDescription,
} from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { ArrowLeft, Search, Users, FileSpreadsheet } from "lucide-vue-next";

const route = useRoute();
const router = useRouter();
const loading = ref(true);
const questionnaireTitle = ref("");
const totalResponses = ref(0);
const respondents = ref<any[]>([]);
const searchQuery = ref("");

onMounted(async () => {
  try {
    const { data } = await api.get(
      `/admin/questionnaires/${route.params.id}/respondents`
    );
    questionnaireTitle.value = data.questionnaire_title;
    totalResponses.value = data.total_responses;
    respondents.value = data.respondents;
  } catch (error) {
    console.error("Failed to fetch respondents", error);
  } finally {
    loading.value = false;
  }
});

const filteredRespondents = computed(() => {
  if (!searchQuery.value) return respondents.value;

  const query = searchQuery.value.toLowerCase();
  return respondents.value.filter(
    (r) =>
      r.nim.toLowerCase().includes(query) ||
      r.nama.toLowerCase().includes(query) ||
      r.prodi.toLowerCase().includes(query)
  );
});

function handleExport() {
  // Export to Excel (could be implemented later)
  const csv = [
    ["NIM", "Nama", "Prodi", "Tahun Lulus", "Tanggal Submit", "Status"],
    ...filteredRespondents.value.map((r) => [
      r.nim,
      r.nama,
      r.prodi,
      r.tahun_lulus,
      r.submitted_at,
      r.status,
    ]),
  ]
    .map((row) => row.join(","))
    .join("\n");

  const blob = new Blob([csv], { type: "text/csv" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `responden_${questionnaireTitle.value.replace(/\s+/g, "_")}.csv`;
  a.click();
  window.URL.revokeObjectURL(url);
}
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Button
          variant="outline"
          size="icon"
          @click="
            router.push(`/admin/questionnaires/${route.params.id}/results`)
          "
        >
          <ArrowLeft class="h-4 w-4" />
        </Button>
        <div class="flex-1">
          <h1 class="text-2xl font-bold tracking-tight">Respondents List</h1>
          <p class="text-muted-foreground">{{ questionnaireTitle }}</p>
        </div>
        <Button variant="outline" @click="handleExport">
          <FileSpreadsheet class="mr-2 h-4 w-4" />
          Export CSV
        </Button>
      </div>

      <!-- Stats Card -->
      <Card>
        <CardHeader>
          <div class="flex items-center gap-2">
            <Users class="h-5 w-5 text-primary" />
            <CardTitle>Total Respondents: {{ totalResponses }}</CardTitle>
          </div>
          <CardDescription>
            List of all alumni who have responded to this questionnaire
          </CardDescription>
        </CardHeader>
      </Card>

      <!-- Search -->
      <div class="flex items-center gap-4">
        <div class="relative flex-1 max-w-sm">
          <Search
            class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground"
          />
          <Input
            v-model="searchQuery"
            placeholder="Search by NIM, name, or prodi..."
            class="pl-9"
          />
        </div>
        <div class="text-sm text-muted-foreground">
          Showing {{ filteredRespondents.length }} of
          {{ totalResponses }} respondents
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div
          class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"
        ></div>
      </div>

      <!-- Table -->
      <Card v-else>
        <CardContent class="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>NIM</TableHead>
                <TableHead>Name</TableHead>
                <TableHead>Study Program</TableHead>
                <TableHead>Graduation Year</TableHead>
                <TableHead>Submitted At</TableHead>
                <TableHead>Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="filteredRespondents.length === 0">
                <TableCell
                  colspan="6"
                  class="text-center py-8 text-muted-foreground"
                >
                  <Users class="h-12 w-12 mx-auto mb-2 opacity-50" />
                  <p>No respondents found</p>
                </TableCell>
              </TableRow>
              <TableRow
                v-for="respondent in filteredRespondents"
                :key="respondent.id"
              >
                <TableCell class="font-medium">{{ respondent.nim }}</TableCell>
                <TableCell>{{ respondent.nama }}</TableCell>
                <TableCell>{{ respondent.prodi }}</TableCell>
                <TableCell>{{ respondent.tahun_lulus }}</TableCell>
                <TableCell>{{ respondent.submitted_at }}</TableCell>
                <TableCell>
                  <Badge
                    :variant="
                      respondent.status === 'complete' ? 'default' : 'secondary'
                    "
                  >
                    {{ respondent.status }}
                  </Badge>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>
