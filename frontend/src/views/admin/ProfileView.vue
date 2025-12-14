<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
  CardDescription,
} from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { toast } from "vue-sonner";
import api from "@/services/api";
import { KeyRound } from "lucide-vue-next";

const router = useRouter();
const authStore = useAuthStore();

const currentPassword = ref("");
const newPassword = ref("");
const newPasswordConfirmation = ref("");
const loading = ref(false);
const errors = ref<Record<string, string[]>>({});

async function handleChangePassword() {
  loading.value = true;
  errors.value = {};

  try {
    await api.put("/profile/password", {
      current_password: currentPassword.value,
      new_password: newPassword.value,
      new_password_confirmation: newPasswordConfirmation.value,
    });

    toast.success("Password berhasil diubah");

    // Clear form
    currentPassword.value = "";
    newPassword.value = "";
    newPasswordConfirmation.value = "";
  } catch (error: any) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
      toast.error("Periksa kembali input Anda");
    } else {
      toast.error(error.response?.data?.message || "Gagal mengubah password");
    }
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <AdminLayout>
    <div class="flex items-center justify-center">
      <h1 class="text-3xl font-bold tracking-tight">Profile</h1>
    </div>

    <div class="grid gap-6 max-w-2xl mx-auto">
      <!-- User Info Card -->
      <Card>
        <CardHeader>
          <CardTitle>Informasi Akun</CardTitle>
          <CardDescription>Detail informasi akun Anda</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="space-y-1">
            <Label class="text-sm text-muted-foreground">Username</Label>
            <p class="text-base font-medium">
              {{ authStore.user?.username || "-" }}
            </p>
          </div>
          <div class="space-y-1">
            <Label class="text-sm text-muted-foreground">Role</Label>
            <p class="text-base font-medium capitalize">
              {{ authStore.user?.role.name || "-" }}
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- Change Password Card -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <KeyRound class="h-5 w-5" />
            Ubah Password
          </CardTitle>
          <CardDescription>
            Pastikan password baru minimal 8 karakter
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="handleChangePassword" class="space-y-4">
            <div class="space-y-2">
              <Label for="current_password">Password Saat Ini</Label>
              <Input
                id="current_password"
                type="password"
                v-model="currentPassword"
                placeholder="Masukkan password saat ini"
                :disabled="loading"
                required
              />
              <p
                v-if="errors.current_password"
                class="text-sm text-destructive"
              >
                {{ errors.current_password[0] }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="new_password">Password Baru</Label>
              <Input
                id="new_password"
                type="password"
                v-model="newPassword"
                placeholder="Masukkan password baru"
                :disabled="loading"
                required
              />
              <p v-if="errors.new_password" class="text-sm text-destructive">
                {{ errors.new_password[0] }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="new_password_confirmation"
                >Konfirmasi Password Baru</Label
              >
              <Input
                id="new_password_confirmation"
                type="password"
                v-model="newPasswordConfirmation"
                placeholder="Ulangi password baru"
                :disabled="loading"
                required
              />
            </div>

            <div class="flex gap-2 pt-2">
              <Button type="submit" :disabled="loading">
                {{ loading ? "Menyimpan..." : "Ubah Password" }}
              </Button>
              <Button
                type="button"
                variant="outline"
                @click="router.push('/admin')"
                :disabled="loading"
              >
                Batal
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>
