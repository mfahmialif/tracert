<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "@/services/api";
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardFooter,
} from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { FileText, ArrowRight } from "lucide-vue-next";

const router = useRouter();
const loading = ref(true);
const questionnaires = ref<any[]>([]);

onMounted(async () => {
  try {
    const { data } = await api.get("/public/questionnaires");
    questionnaires.value = data.questionnaires;
  } catch (error) {
    console.error("Failed to fetch questionnaires", error);
  } finally {
    loading.value = false;
  }
});

function startSurvey(id: number) {
  router.push(`/public/questionnaires/${id}`);
}
</script>

<template>
  <div
    class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-purple-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950"
  >
    <!-- Header -->
    <header
      class="border-b bg-white/80 dark:bg-slate-950/80 backdrop-blur-xl sticky top-0 z-50"
    >
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div
              class="p-2 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl"
            >
              <FileText class="h-6 w-6 text-white" />
            </div>
            <div>
              <h1
                class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent"
              >
                Public Surveys
              </h1>
              <p class="text-xs text-muted-foreground">
                UII Darullughah Wadda'wah
              </p>
            </div>
          </div>
          <Button variant="outline" @click="router.push('/')">
            Back to Home
          </Button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-12">
      <!-- Hero Section -->
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-5xl font-bold mb-4">
          <span
            class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent"
          >
            Available Surveys
          </span>
        </h2>
        <p class="text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
          Participate in our surveys and help us improve our services. Your
          feedback matters!
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div
          class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"
        ></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="questionnaires.length === 0" class="text-center py-12">
        <FileText
          class="h-24 w-24 mx-auto mb-4 text-muted-foreground opacity-50"
        />
        <h3 class="text-xl font-semibold mb-2">No Surveys Available</h3>
        <p class="text-muted-foreground">
          There are currently no public surveys available. Please check back
          later.
        </p>
      </div>

      <!-- Questionnaires Grid -->
      <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card
          v-for="q in questionnaires"
          :key="q.id"
          class="group hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 cursor-pointer"
          @click="startSurvey(q.id)"
        >
          <CardHeader>
            <div class="flex items-start justify-between mb-2">
              <Badge variant="outline" class="mb-2">
                {{ q.year }}
              </Badge>
              <Badge variant="secondary" class="text-xs">
                <FileText class="h-3 w-3 mr-1" />
                {{ q.questions_count }} questions
              </Badge>
            </div>
            <CardTitle
              class="text-xl group-hover:text-primary transition-colors"
            >
              {{ q.title }}
            </CardTitle>
            <CardDescription class="line-clamp-2">
              {{ q.description || "No description available" }}
            </CardDescription>
          </CardHeader>
          <CardFooter>
            <Button
              class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white group-hover:shadow-lg transition-all"
              @click.stop="startSurvey(q.id)"
            >
              Start Survey
              <ArrowRight
                class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform"
              />
            </Button>
          </CardFooter>
        </Card>
      </div>
    </main>

    <!-- Footer -->
    <footer
      class="border-t bg-white/50 dark:bg-slate-950/50 backdrop-blur-xl mt-20"
    >
      <div
        class="container mx-auto px-6 py-8 text-center text-sm text-muted-foreground"
      >
        <p>&copy; 2025 Tracer Study UII Dalwa. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>
