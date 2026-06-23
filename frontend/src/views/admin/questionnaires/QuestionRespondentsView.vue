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
import {
  ArrowLeft,
  Search,
  MessageSquare,
  FileSpreadsheet,
} from "lucide-vue-next";

const route = useRoute();
const router = useRouter();
const loading = ref(true);
const questionnaireTitle = ref("");
const questionText = ref("");
const questionType = ref("");
const totalAnswers = ref(0);
const questionnaireIsPublic = ref(true);
const answers = ref<any[]>([]);
const searchQuery = ref("");

onMounted(async () => {
  try {
    const { data } = await api.get(
      `/admin/questionnaires/${route.params.id}/questions/${route.params.questionId}/respondents`
    );
    questionnaireTitle.value = data.questionnaire_title;
    questionText.value = data.question_text;
    questionType.value = data.question_type;
    totalAnswers.value = data.total_answers;
    questionnaireIsPublic.value = data.is_public ?? true;
    answers.value = data.answers;
  } catch (error) {
    console.error("Failed to fetch question respondents", error);
  } finally {
    loading.value = false;
  }
});

const filteredAnswers = computed(() => {
  if (!searchQuery.value) return answers.value;

  const query = searchQuery.value.toLowerCase();
  return answers.value.filter(
    (a) => {
      if (questionnaireIsPublic.value) {
        return (
          String(a.nim ?? "").toLowerCase().includes(query) ||
          String(a.nama ?? "").toLowerCase().includes(query) ||
          String(a.answer ?? "").toLowerCase().includes(query)
        );
      }

      return (
        String(a.answer ?? "").toLowerCase().includes(query) ||
        String(a.submitted_at ?? "").toLowerCase().includes(query)
      );
    }
  );
});

function handleExport() {
  const csv = [
    ["NIM", "Nama", "Prodi", "Tahun Lulus", "Jawaban", "Tanggal Submit"],
    ...filteredAnswers.value.map((a) => [
      a.nim,
      a.nama,
      a.prodi,
      a.tahun_lulus,
      a.answer,
      a.submitted_at,
    ]),
  ]
    .map((row) => row.join(","))
    .join("\n");

  const blob = new Blob([csv], { type: "text/csv" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `jawaban_${questionText.value.substring(0, 30).replace(/\s+/g, "_")}.csv`;
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
          <h1 class="text-2xl font-bold tracking-tight">
            Question Respondents
          </h1>
          <p class="text-sm text-muted-foreground">{{ questionnaireTitle }}</p>
        </div>
        <Button variant="outline" @click="handleExport">
          <FileSpreadsheet class="mr-2 h-4 w-4" />
          Export CSV
        </Button>
      </div>

      <!-- Question Card -->
      <Card>
        <CardHeader>
          <div class="flex items-start gap-2">
            <MessageSquare class="h-5 w-5 text-primary mt-1" />
            <div class="flex-1">
              <CardTitle class="text-lg">{{ questionText }}</CardTitle>
              <CardDescription class="mt-2">
                Type:
                <span class="font-medium capitalize">{{ questionType }}</span> •
                Total Answers:
                <span class="font-medium">{{ totalAnswers }}</span>
              </CardDescription>
            </div>
          </div>
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
            :placeholder="
              questionnaireIsPublic
                ? 'Search by NIM, name, or answer...'
                : 'Search by answer or submit time...'
            "
            class="pl-9"
          />
        </div>
        <div class="text-sm text-muted-foreground">
          Showing {{ filteredAnswers.length }} of {{ totalAnswers }} answers
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
                <TableHead v-if="questionnaireIsPublic" class="w-[120px]">
                  NIM
                </TableHead>
                <TableHead v-if="questionnaireIsPublic" class="w-[200px]">
                  Name
                </TableHead>
                <TableHead v-if="questionnaireIsPublic" class="w-[180px]">
                  Study Program
                </TableHead>
                <TableHead v-if="questionnaireIsPublic" class="w-[100px]">
                  Year
                </TableHead>
                <TableHead>Answer</TableHead>
                <TableHead class="w-[180px]">Submitted At</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="filteredAnswers.length === 0">
                <TableCell
                  :colspan="questionnaireIsPublic ? 6 : 2"
                  class="text-center py-8 text-muted-foreground"
                >
                  <MessageSquare class="h-12 w-12 mx-auto mb-2 opacity-50" />
                  <p>No answers found</p>
                </TableCell>
              </TableRow>
              <TableRow
                v-for="answer in filteredAnswers"
                :key="answer.id"
                class="hover:bg-muted/50"
              >
                <TableCell v-if="questionnaireIsPublic" class="font-medium">
                  {{ answer.nim }}
                </TableCell>
                <TableCell v-if="questionnaireIsPublic">
                  {{ answer.nama }}
                </TableCell>
                <TableCell v-if="questionnaireIsPublic">
                  {{ answer.prodi }}
                </TableCell>
                <TableCell v-if="questionnaireIsPublic">
                  {{ answer.tahun_lulus }}
                </TableCell>
                <TableCell class="max-w-md">
                  <div class="line-clamp-2" :title="answer.answer">
                    {{ answer.answer || "-" }}
                  </div>
                </TableCell>
                <TableCell class="text-sm text-muted-foreground">
                  {{ answer.submitted_at }}
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>
