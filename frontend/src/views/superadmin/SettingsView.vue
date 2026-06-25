<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useSettingsStore } from '@/stores/settings';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from 'vue-sonner';
import { Save } from 'lucide-vue-next';

const settingsStore = useSettingsStore();
const enableAlumniStatus = ref(false);
const saving = ref(false);

onMounted(async () => {
  await settingsStore.fetchSettings();
  enableAlumniStatus.value = settingsStore.isAlumniStatusEnabled;
});

async function saveSettings() {
  saving.value = true;
  try {
    await settingsStore.updateSettings({
      enable_alumni_status: enableAlumniStatus.value ? 'true' : 'false',
    });
    toast.success('Konfigurasi berhasil disimpan');
  } catch (error) {
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
                <Label for="enable-status" class="text-base font-semibold">Gunakan Kolom Status Alumni</Label>
                <p class="text-sm text-muted-foreground">
                  Jika diaktifkan, kolom "Status" (Bekerja, Belum Bekerja, dll) akan dimunculkan di form alumni, tabel data, fitur ekspor/impor, dan informasi publik.
                </p>
              </div>
              <Switch id="enable-status" :checked="enableAlumniStatus" @update:checked="v => enableAlumniStatus = v" />
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
