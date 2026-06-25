<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useSettingsStore } from '@/stores/settings';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from 'vue-sonner';
import { Save } from 'lucide-vue-next';

const settingsStore = useSettingsStore();
const enableAlumniStatus = ref(false);
const saving = ref(false);
const loaded = ref(false);

onMounted(async () => {
  await settingsStore.fetchSettings();
  enableAlumniStatus.value = settingsStore.isAlumniStatusEnabled;
  loaded.value = true;
});

function toggleStatus() {
  enableAlumniStatus.value = !enableAlumniStatus.value;
}

async function saveSettings() {
  saving.value = true;
  try {
    await settingsStore.updateSettings({
      enable_alumni_status: enableAlumniStatus.value ? 'true' : 'false',
    });
    toast.success('Konfigurasi berhasil disimpan');
  } catch (error: any) {
    toast.error('Gagal menyimpan konfigurasi');
  } finally {
    saving.value = false;
  }
}
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Konfigurasi Aplikasi</h1>
        <p class="text-muted-foreground">Kelola pengaturan global untuk sistem Tracer Study.</p>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Pengaturan Alumni</CardTitle>
            <CardDescription>
              Atur fitur dan tampilan yang berkaitan dengan entitas Alumni.
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
            <div class="flex items-center justify-between space-x-2">
              <div class="space-y-1">
                <Label class="text-base font-semibold cursor-pointer" @click="toggleStatus">
                  Gunakan Kolom Status Alumni
                </Label>
                <p class="text-sm text-muted-foreground">
                  Jika diaktifkan, kolom "Status" (Bekerja, Belum Bekerja, dll) akan dimunculkan di form alumni, tabel data, fitur ekspor/impor, dan informasi publik.
                </p>
              </div>
              <!-- Custom toggle switch using native checkbox -->
              <button
                v-if="loaded"
                type="button"
                role="switch"
                :aria-checked="enableAlumniStatus"
                @click="toggleStatus"
                :class="[
                  'peer relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent shadow-sm transition-colors duration-200 ease-in-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background',
                  enableAlumniStatus ? 'bg-primary' : 'bg-input'
                ]"
              >
                <span
                  :class="[
                    'pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform duration-200 ease-in-out',
                    enableAlumniStatus ? 'translate-x-5' : 'translate-x-0'
                  ]"
                />
              </button>
            </div>
            
            <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
              <Button @click="saveSettings" :disabled="saving" class="gap-2">
                <Save class="w-4 h-4" />
                {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AdminLayout>
</template>
