<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import api from "@/services/api";
import AdminLayout from "@/layouts/AdminLayout.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Dialog,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogScrollContent,
  DialogTitle,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { toast } from "vue-sonner";
import { ChevronLeft, ChevronRight, Pencil, Plus, Search, ShieldCheck, Trash2, Users } from "lucide-vue-next";

type Role = {
  role_id: number;
  role_name: string;
  users_count: number;
};

type ManagedUser = {
  id: number;
  username: string;
  role_id: number;
  role: Role;
  alumni_count: number;
};

const users = ref<ManagedUser[]>([]);
const roles = ref<Role[]>([]);
const loading = ref(true);
const saving = ref(false);
const dialogOpen = ref(false);
const editingUser = ref<ManagedUser | null>(null);
const search = ref("");
const selectedRole = ref("all");
const formError = ref("");
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });

const form = ref({
  username: "",
  password: "",
  password_confirmation: "",
  role_id: "2",
});

const assignableRoles = computed(() =>
  editingUser.value ? roles.value : roles.value.filter((role) => role.role_name !== "alumni")
);

async function fetchRoles() {
  const { data } = await api.get("/admin/users/roles");
  roles.value = data.data;
}

async function fetchUsers(page = pagination.value.current_page) {
  loading.value = true;
  try {
    const { data } = await api.get("/admin/users", {
      params: {
        page,
        per_page: 15,
        search: search.value,
        role_id: selectedRole.value === "all" ? undefined : selectedRole.value,
      },
    });
    users.value = data.data;
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      total: data.total,
    };
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  try {
    await Promise.all([fetchRoles(), fetchUsers(1)]);
  } catch (error) {
    console.error("Failed to load user management", error);
    toast.error("Gagal memuat data user dan level.");
  }
});

function openCreateDialog() {
  editingUser.value = null;
  formError.value = "";
  form.value = {
    username: "",
    password: "",
    password_confirmation: "",
    role_id: "2",
  };
  dialogOpen.value = true;
}

function openEditDialog(user: ManagedUser) {
  editingUser.value = user;
  formError.value = "";
  form.value = {
    username: user.username,
    password: "",
    password_confirmation: "",
    role_id: user.role_id.toString(),
  };
  dialogOpen.value = true;
}

async function saveUser() {
  saving.value = true;
  formError.value = "";

  try {
    const payload = {
      ...form.value,
      role_id: Number(form.value.role_id),
      password: form.value.password || undefined,
      password_confirmation: form.value.password_confirmation || undefined,
    };

    if (editingUser.value) {
      await api.put(`/admin/users/${editingUser.value.id}`, payload);
      toast.success("User dan level berhasil diperbarui.");
    } else {
      await api.post("/admin/users", payload);
      toast.success("User berhasil dibuat.");
    }

    dialogOpen.value = false;
    await Promise.all([fetchRoles(), fetchUsers()]);
  } catch (error: any) {
    const errors = error.response?.data?.errors;
    formError.value = errors
      ? Object.values(errors).flat().join(" ")
      : error.response?.data?.message || "Gagal menyimpan user.";
  } finally {
    saving.value = false;
  }
}

async function deleteUser(user: ManagedUser) {
  if (!window.confirm(`Hapus user “${user.username}”? Tindakan ini tidak dapat dibatalkan.`)) {
    return;
  }

  try {
    await api.delete(`/admin/users/${user.id}`);
    toast.success("User berhasil dihapus.");
    await Promise.all([fetchRoles(), fetchUsers()]);
  } catch (error: any) {
    const errors = error.response?.data?.errors;
    toast.error(
      errors
        ? Object.values(errors).flat().join(" ")
        : error.response?.data?.message || "Gagal menghapus user."
    );
  }
}

function roleLabel(name: string) {
  return name === "superadmin" ? "Superadmin" : name === "admin" ? "Admin" : "Alumni";
}
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">
      <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Manajemen User & Level</h1>
          <p class="text-muted-foreground">Kelola akun aplikasi dan level aksesnya.</p>
        </div>
        <Button class="bg-emerald-600 text-white hover:bg-emerald-700" @click="openCreateDialog">
          <Plus class="mr-2 h-4 w-4" /> Tambah User
        </Button>
      </div>

      <div class="grid gap-4 sm:grid-cols-3">
        <Card v-for="role in roles" :key="role.role_id">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">{{ roleLabel(role.role_name) }}</CardTitle>
            <ShieldCheck v-if="role.role_name === 'superadmin'" class="h-4 w-4 text-emerald-600" />
            <Users v-else class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold">{{ role.users_count }}</p>
            <p class="text-xs text-muted-foreground">akun pada level ini</p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardContent class="pt-6">
          <div class="mb-5 flex flex-col gap-3 md:flex-row">
            <div class="relative flex-1">
              <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
              <Input
                v-model="search"
                class="pl-9"
                placeholder="Cari username atau level..."
                @keyup.enter="fetchUsers(1)"
              />
            </div>
            <Select v-model="selectedRole" @update:model-value="fetchUsers(1)">
              <SelectTrigger class="w-full md:w-52">
                <SelectValue placeholder="Semua level" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Semua level</SelectItem>
                <SelectItem v-for="role in roles" :key="role.role_id" :value="role.role_id.toString()">
                  {{ roleLabel(role.role_name) }}
                </SelectItem>
              </SelectContent>
            </Select>
            <Button variant="outline" @click="fetchUsers(1)">Cari</Button>
          </div>

          <div class="overflow-x-auto rounded-xl border">
            <table class="w-full text-sm">
              <thead class="bg-muted/60 text-left">
                <tr>
                  <th class="px-4 py-3 font-medium">Username</th>
                  <th class="px-4 py-3 font-medium">Level</th>
                  <th class="px-4 py-3 font-medium">Profil Alumni</th>
                  <th class="px-4 py-3 text-right font-medium">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="4" class="px-4 py-10 text-center text-muted-foreground">Memuat data...</td>
                </tr>
                <tr v-else-if="users.length === 0">
                  <td colspan="4" class="px-4 py-10 text-center text-muted-foreground">User tidak ditemukan.</td>
                </tr>
                <tr v-for="user in users" v-else :key="user.id" class="border-t">
                  <td class="px-4 py-3 font-medium">{{ user.username }}</td>
                  <td class="px-4 py-3">
                    <Badge :variant="user.role.role_name === 'superadmin' ? 'default' : 'secondary'">
                      {{ roleLabel(user.role.role_name) }}
                    </Badge>
                  </td>
                  <td class="px-4 py-3 text-muted-foreground">
                    {{ user.alumni_count ? "Terhubung" : "-" }}
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex justify-end gap-2">
                      <Button variant="outline" size="icon" @click="openEditDialog(user)">
                        <Pencil class="h-4 w-4" />
                      </Button>
                      <Button variant="outline" size="icon" class="text-destructive" @click="deleteUser(user)">
                        <Trash2 class="h-4 w-4" />
                      </Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-4 flex items-center justify-between">
            <p class="text-sm text-muted-foreground">{{ pagination.total }} user</p>
            <div class="flex items-center gap-2">
              <Button
                variant="outline"
                size="icon"
                :disabled="pagination.current_page <= 1"
                @click="fetchUsers(pagination.current_page - 1)"
              >
                <ChevronLeft class="h-4 w-4" />
              </Button>
              <span class="text-sm">{{ pagination.current_page }} / {{ pagination.last_page }}</span>
              <Button
                variant="outline"
                size="icon"
                :disabled="pagination.current_page >= pagination.last_page"
                @click="fetchUsers(pagination.current_page + 1)"
              >
                <ChevronRight class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <Dialog v-model:open="dialogOpen">
        <DialogScrollContent class="max-w-xl">
          <DialogHeader>
            <DialogTitle>{{ editingUser ? "Edit User & Level" : "Tambah User" }}</DialogTitle>
            <DialogDescription>
              Akun alumni baru tetap dibuat melalui modul Alumni agar data akademiknya lengkap.
            </DialogDescription>
          </DialogHeader>

          <div class="space-y-4">
            <div v-if="formError" class="rounded-xl bg-destructive/10 p-3 text-sm text-destructive">
              {{ formError }}
            </div>

            <div class="grid gap-2">
              <Label for="username">Username</Label>
              <Input id="username" v-model="form.username" maxlength="50" />
            </div>

            <div class="grid gap-2">
              <Label for="level">Level akses</Label>
              <Select v-model="form.role_id">
                <SelectTrigger id="level"><SelectValue placeholder="Pilih level" /></SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="role in assignableRoles"
                    :key="role.role_id"
                    :value="role.role_id.toString()"
                  >
                    {{ roleLabel(role.role_name) }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="grid gap-2">
              <Label for="password">
                Password {{ editingUser ? "(kosongkan jika tidak diubah)" : "" }}
              </Label>
              <Input id="password" v-model="form.password" type="password" minlength="8" />
            </div>

            <div class="grid gap-2">
              <Label for="password-confirmation">Konfirmasi password</Label>
              <Input
                id="password-confirmation"
                v-model="form.password_confirmation"
                type="password"
                minlength="8"
              />
            </div>
          </div>

          <DialogFooter>
            <Button variant="outline" :disabled="saving" @click="dialogOpen = false">Batal</Button>
            <Button class="bg-emerald-600 text-white hover:bg-emerald-700" :disabled="saving" @click="saveUser">
              {{ saving ? "Menyimpan..." : "Simpan" }}
            </Button>
          </DialogFooter>
        </DialogScrollContent>
      </Dialog>
    </div>
  </AdminLayout>
</template>
