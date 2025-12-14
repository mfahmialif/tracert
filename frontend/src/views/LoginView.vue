<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useTheme } from "../composables/useTheme";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  CardFooter,
} from "@/components/ui/card";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import {
  Sun,
  Moon,
  Eye,
  EyeOff,
  AlertCircle,
  GraduationCap,
} from "lucide-vue-next";

const router = useRouter();
const authStore = useAuthStore();
const { isDark, toggleTheme } = useTheme();

const username = ref("");
const password = ref("");
const showPassword = ref(false);

async function handleLogin() {
  const success = await authStore.login(username.value, password.value);
  if (success) {
    router.push(authStore.isAdmin ? "/admin" : "/home");
  }
}
</script>

<template>
  <div
    class="min-h-screen flex items-center justify-center p-4 bg-background transition-colors duration-300 relative overflow-hidden"
  >
    <!-- Background Gradients -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div
        class="absolute -top-40 -right-40 w-96 h-96 bg-primary/10 rounded-full blur-3xl animate-pulse"
      ></div>
      <div
        class="absolute -bottom-40 -left-40 w-96 h-96 bg-primary/10 rounded-full blur-3xl animate-pulse delay-1000"
      ></div>
    </div>

    <!-- Theme Toggle -->
    <Button
      variant="ghost"
      size="icon"
      class="absolute top-4 right-4 z-10 rounded-full"
      @click="toggleTheme"
    >
      <Sun v-if="isDark" class="h-5 w-5 transition-all" />
      <Moon v-else class="h-5 w-5 transition-all" />
    </Button>

    <Card
      class="w-full max-w-md relative z-10 border-border/50 shadow-xl backdrop-blur-sm bg-card/80"
    >
      <CardHeader class="space-y-1 text-center">
        <div class="flex justify-center mb-4">
          <div class="p-3 bg-primary/10 rounded-full">
            <GraduationCap class="w-10 h-10 text-primary" />
          </div>
        </div>
        <CardTitle
          class="text-2xl font-bold bg-gradient-to-r from-primary to-purple-600 bg-clip-text text-transparent"
        >
          Tracer Study
        </CardTitle>
        <CardDescription> Silakan login untuk melanjutkan </CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="handleLogin" class="space-y-4">
          <div class="space-y-2">
            <Label for="username">Username / NIM</Label>
            <Input
              id="username"
              v-model="username"
              placeholder="Masukkan username"
              required
              class="bg-background/50"
            />
          </div>
          <div class="space-y-2">
            <Label for="password">Password</Label>
            <div class="relative">
              <Input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Masukkan password"
                required
                class="pr-10 bg-background/50"
              />
              <Button
                type="button"
                variant="ghost"
                size="icon"
                class="absolute right-0 top-0 h-full px-3 hover:bg-transparent"
                @click="showPassword = !showPassword"
              >
                <EyeOff
                  v-if="showPassword"
                  class="h-4 w-4 text-muted-foreground"
                />
                <Eye v-else class="h-4 w-4 text-muted-foreground" />
              </Button>
            </div>
          </div>

          <Alert v-if="authStore.error" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Error</AlertTitle>
            <AlertDescription>{{ authStore.error }}</AlertDescription>
          </Alert>

          <Button type="submit" class="w-full" :disabled="authStore.loading">
            <span
              v-if="authStore.loading"
              class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"
            ></span>
            {{ authStore.loading ? "Memproses..." : "Masuk" }}
          </Button>
        </form>
      </CardContent>
      <CardFooter>
        <p class="text-xs text-center text-muted-foreground w-full">
          &copy; Tracer Study • UII Dalwa
        </p>
      </CardFooter>
    </Card>
  </div>
</template>
