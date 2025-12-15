<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "@/services/api";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
  CardDescription,
} from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Checkbox } from "@/components/ui/checkbox";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { toast } from "vue-sonner";
import { ArrowLeft, CheckCircle2, Send } from "lucide-vue-next";

const route = useRoute();
const router = useRouter();
const loading = ref(true);
const submitting = ref(false);
const submitted = ref(false);
const questionnaire = ref<any>(null);
const respondentInfo = ref({
  name: "",
  email: "",
  phone: "",
});
const answers = ref<Record<number, any>>({});

onMounted(async () => {
  try {
    const { data } = await api.get(`/public/questionnaires/${route.params.id}`);
    questionnaire.value = data.data;
  } catch (error: any) {
    console.error("Failed to fetch questionnaire", error);
    toast.error(
      error.response?.data?.message || "Failed to load questionnaire"
    );
  } finally {
    loading.value = false;
  }
});

const allRequiredAnswered = computed(() => {
  if (!questionnaire.value) return false;

  const allQuestions = questionnaire.value.sections.flatMap(
    (s: any) => s.questions
  );
  const requiredQuestions = allQuestions.filter((q: any) => q.is_required);

  return requiredQuestions.every((q: any) => {
    const answer = answers.value[q.id];
    if (Array.isArray(answer)) {
      return answer.length > 0;
    }
    return answer != null && answer !== "";
  });
});

async function handleSubmit() {
  if (!respondentInfo.value.name || !respondentInfo.value.email) {
    toast.error("Please fill in your name and email");
    return;
  }

  if (!allRequiredAnswered.value) {
    toast.error("Please answer all required questions");
    return;
  }

  submitting.value = true;
  try {
    const payload = {
      respondent_name: respondentInfo.value.name,
      respondent_email: respondentInfo.value.email,
      respondent_phone: respondentInfo.value.phone,
      answers: Object.entries(answers.value).map(
        ([questionId, answerValue]) => ({
          question_id: parseInt(questionId),
          answer_value: answerValue,
        })
      ),
    };

    await api.post(`/public/questionnaires/${route.params.id}/submit`, payload);
    submitted.value = true;
    toast.success("Response submitted successfully!");
  } catch (error: any) {
    console.error("Failed to submit", error);
    toast.error(error.response?.data?.message || "Failed to submit response");
  } finally {
    submitting.value = false;
  }
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
        <Button variant="ghost" @click="router.push('/public/questionnaires')">
          <ArrowLeft class="mr-2 h-4 w-4" />
          Back to Surveys
        </Button>
      </div>
    </header>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-20">
      <div
        class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"
      ></div>
    </div>

    <!-- Success State -->
    <div v-else-if="submitted" class="container mx-auto px-6 py-20 text-center">
      <CheckCircle2 class="h-24 w-24 mx-auto mb-6 text-green-600" />
      <h2 class="text-3xl font-bold mb-4">Thank You!</h2>
      <p class="text-lg text-muted-foreground mb-8">
        Your response has been successfully submitted.
      </p>
      <Button @click="router.push('/public/questionnaires')">
        Back to Surveys
      </Button>
    </div>

    <!-- Form -->
    <main
      v-else-if="questionnaire"
      class="container mx-auto px-6 py-12 max-w-4xl"
    >
      <Card class="mb-8">
        <CardHeader>
          <CardTitle class="text-2xl">{{ questionnaire.title }}</CardTitle>
          <CardDescription>
            {{ questionnaire.description }}
          </CardDescription>
          <div class="flex gap-4 text-sm text-muted-foreground mt-4">
            <span>Year: {{ questionnaire.year }}</span>
            <span>•</span>
            <span>Deadline: {{ questionnaire.end_date }}</span>
          </div>
        </CardHeader>
      </Card>

      <!-- Respondent Info -->
      <Card class="mb-8">
        <CardHeader>
          <CardTitle>Your Information</CardTitle>
          <CardDescription>Please provide your contact details</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div>
            <Label for="name">Name <span class="text-red-500">*</span></Label>
            <Input
              id="name"
              v-model="respondentInfo.name"
              placeholder="Enter your full name"
              required
            />
          </div>
          <div>
            <Label for="email">Email <span class="text-red-500">*</span></Label>
            <Input
              id="email"
              v-model="respondentInfo.email"
              type="email"
              placeholder="your.email@example.com"
              required
            />
          </div>
          <div>
            <Label for="phone">Phone Number</Label>
            <Input
              id="phone"
              v-model="respondentInfo.phone"
              type="tel"
              placeholder="+62 xxx xxxx xxxx"
            />
          </div>
        </CardContent>
      </Card>

      <!-- Questions -->
      <div
        v-for="(section, sIdx) in questionnaire.sections"
        :key="sIdx"
        class="mb-8"
      >
        <h3 class="text-lg font-semibold mb-4">
          Section {{ section.section }}
        </h3>

        <Card
          v-for="(question, qIdx) in section.questions"
          :key="question.id"
          class="mb-6"
        >
          <CardHeader>
            <CardTitle class="text-base font-medium">
              {{ qIdx + 1 }}. {{ question.text }}
              <span v-if="question.is_required" class="text-red-500 ml-1"
                >*</span
              >
            </CardTitle>
          </CardHeader>
          <CardContent>
            <!-- Text Input -->
            <Input
              v-if="question.type === 'text'"
              v-model="answers[question.id]"
              placeholder="Your answer"
            />

            <!-- Long Text -->
            <Textarea
              v-else-if="question.type === 'long_text'"
              v-model="answers[question.id]"
              placeholder="Your answer"
              rows="4"
            />

            <!-- Radio -->
            <RadioGroup
              v-else-if="question.type === 'radio'"
              v-model="answers[question.id]"
            >
              <div
                v-for="(option, oIdx) in question.options"
                :key="oIdx"
                class="flex items-center space-x-2 mb-2"
              >
                <RadioGroupItem
                  :value="option"
                  :id="`q${question.id}-${oIdx}`"
                />
                <Label :for="`q${question.id}-${oIdx}`">{{ option }}</Label>
              </div>
            </RadioGroup>

            <!-- Checkbox -->
            <div v-else-if="question.type === 'checkbox'" class="space-y-2">
              <div
                v-for="(option, oIdx) in question.options"
                :key="oIdx"
                class="flex items-center space-x-2"
              >
                <Checkbox
                  :id="`q${question.id}-${oIdx}`"
                  :checked="(answers[question.id] || []).includes(option)"
                  @update:checked="
                    (checked) => {
                      if (!answers[question.id]) answers[question.id] = [];
                      if (checked) {
                        answers[question.id].push(option);
                      } else {
                        answers[question.id] = answers[question.id].filter(
                          (v: string) => v !== option
                        );
                      }
                    }
                  "
                />
                <Label :for="`q${question.id}-${oIdx}`">{{ option }}</Label>
              </div>
            </div>

            <!-- Select -->
            <Select
              v-else-if="question.type === 'select'"
              v-model="answers[question.id]"
            >
              <SelectTrigger>
                <SelectValue placeholder="Select an option" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="(option, oIdx) in question.options"
                  :key="oIdx"
                  :value="option"
                >
                  {{ option }}
                </SelectItem>
              </SelectContent>
            </Select>

            <!-- Scale -->
            <RadioGroup
              v-else-if="question.type === 'scale'"
              v-model="answers[question.id]"
              class="flex gap-4"
            >
              <div
                v-for="(option, oIdx) in question.options"
                :key="oIdx"
                class="flex flex-col items-center"
              >
                <RadioGroupItem
                  :value="option"
                  :id="`q${question.id}-${oIdx}`"
                />
                <Label :for="`q${question.id}-${oIdx}`" class="mt-1">{{
                  option
                }}</Label>
              </div>
            </RadioGroup>

            <!-- Date -->
            <Input
              v-else-if="question.type === 'date'"
              v-model="answers[question.id]"
              type="date"
            />
          </CardContent>
        </Card>
      </div>

      <!-- Submit Button -->
      <Card>
        <CardContent class="pt-6">
          <Button
            @click="handleSubmit"
            :disabled="submitting || !allRequiredAnswered"
            class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white"
            size="lg"
          >
            <Send class="mr-2 h-5 w-5" />
            {{ submitting ? "Submitting..." : "Submit Response" }}
          </Button>
          <p
            v-if="!allRequiredAnswered"
            class="text-sm text-red-500 text-center mt-2"
          >
            Please answer all required questions (*)
          </p>
        </CardContent>
      </Card>
    </main>
  </div>
</template>
