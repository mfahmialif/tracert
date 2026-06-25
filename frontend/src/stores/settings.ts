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
      return state.settings['enable_alumni_status'] === 'true';
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
      try {
        await api.post('/admin/settings', { settings: newSettings });
        // Update local state
        this.settings = { ...this.settings, ...newSettings };
        return true;
      } catch (error) {
        console.error('Failed to update settings', error);
        throw error;
      }
    }
  }
});
