import { defineStore } from 'pinia';
import api from '@/services/api';

interface SettingsState {
  settings: Record<string, string>;
  loading: boolean;
}

export const useSettingsStore = defineStore('settings', {
  state: (): SettingsState => ({
    settings: {},
    loading: false,
  }),
  getters: {
    getSetting: (state) => (key: string) => {
      return state.settings[key];
    },
    isAlumniStatusEnabled: (state) => {
      const val = state.settings['enable_alumni_status'];
      return val === 'true' || val === '1' || val === true;
    }
  },
  actions: {
    async fetchSettings() {
      this.loading = true;
      try {
        const response = await api.get('/settings');
        this.settings = response.data;
      } catch (error) {
        console.error('Failed to fetch settings', error);
      } finally {
        this.loading = false;
      }
    },
    async updateSettings(newSettings: Record<string, string>) {
      await api.post('/admin/settings', { settings: newSettings });
      this.settings = { ...this.settings, ...newSettings };
    }
  }
});
