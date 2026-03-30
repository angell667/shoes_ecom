<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const loading = ref(true)
const saving = ref(false)
const message = ref('')
const error = ref('')

const settings = ref({
  google_enabled: '1',
  google_client_id: '',
  google_client_secret: '',
  google_redirect_uri: 'http://localhost:8000/api/auth/google/callback'
})

const oauthProviders = ref([
  {
    id: 'google',
    name: 'Google',
    icon: `<svg viewBox="0 0 24 24" width="24" height="24">
      <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
      <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
      <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
      <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
    </svg>`,
    description: 'Allow users to sign in with their Google account'
  }
])

const showSecret = ref(false)

const toggleSecret = () => {
  showSecret.value = !showSecret.value
}

const copyToClipboard = async (text) => {
  try {
    await navigator.clipboard.writeText(text)
    message.value = 'Copied to clipboard!'
    setTimeout(() => message.value = '', 2000)
  } catch (err) {
    error.value = 'Failed to copy'
  }
}

const fetchSettings = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await axios.get('/api/admin/settings/oauth')
    settings.value = { ...settings.value, ...response.data }
  } catch (err) {
    error.value = 'Failed to load settings'
    console.error('Fetch settings error:', err)
  } finally {
    loading.value = false
  }
}

const saveSettings = async () => {
  saving.value = true
  error.value = ''
  message.value = ''
  
  try {
    await axios.post('/api/admin/settings/oauth', settings.value)
    message.value = 'Settings saved successfully! OAuth will work after restart.'
    setTimeout(() => message.value = '', 5000)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save settings'
    console.error('Save settings error:', err)
  } finally {
    saving.value = false
  }
}

onMounted(fetchSettings)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h1 class="page-title">OAuth Settings</h1>
        <p class="page-subtitle">Configure social login providers for your store</p>
      </div>
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
    </div>

    <template v-else>
      <!-- Instructions Card -->
      <div class="card instruction-card">
        <div class="card-header">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 16v-4"/>
            <path d="M12 8h.01"/>
          </svg>
          <h3>Setup Instructions</h3>
        </div>
        <div class="instruction-content">
          <ol>
            <li>Go to <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a></li>
            <li>Create a new project or select an existing one</li>
            <li>Enable the Google+ API or People API</li>
            <li>Create OAuth 2.0 credentials (Web application)</li>
            <li>Add <strong>Authorized JavaScript origin</strong>: <code>http://localhost:5173</code></li>
            <li>Add <strong>Authorized redirect URI</strong>: <code>{{ settings.google_redirect_uri }}</code></li>
            <li>Copy the Client ID and Client Secret below and save</li>
          </ol>
        </div>
      </div>

      <!-- OAuth Providers -->
      <div class="providers-grid">
        <div v-for="provider in oauthProviders" :key="provider.id" class="card provider-card">
          <div class="provider-header">
            <div class="provider-info">
              <div class="provider-icon" v-html="provider.icon"></div>
              <div>
                <h3>{{ provider.name }}</h3>
                <p>{{ provider.description }}</p>
              </div>
            </div>
            <label class="toggle">
              <input type="checkbox" :checked="settings.google_enabled === '1'" @change="settings.google_enabled = $event.target.checked ? '1' : '0'" />
              <span class="toggle-slider"></span>
            </label>
          </div>

          <div class="provider-form">
            <div class="form-group">
              <label>Client ID</label>
              <div class="input-group">
                <input 
                  v-model="settings.google_client_id" 
                  type="text" 
                  placeholder="Enter your Google Client ID"
                />
                <button @click="copyToClipboard(settings.google_client_id)" class="copy-btn" title="Copy">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                  </svg>
                </button>
              </div>
            </div>

            <div class="form-group">
              <label>Client Secret</label>
              <div class="input-group">
                <input 
                  v-model="settings.google_client_secret" 
                  :type="showSecret ? 'text' : 'password'" 
                  placeholder="Enter your Google Client Secret"
                />
                <button @click="toggleSecret" class="copy-btn" title="Toggle visibility">
                  <svg v-if="!showSecret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                  </svg>
                </button>
                <button @click="copyToClipboard(settings.google_client_secret)" class="copy-btn" title="Copy">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                  </svg>
                </button>
              </div>
            </div>

            <div class="form-group">
              <label>Redirect URI</label>
              <div class="input-group">
                <input 
                  v-model="settings.google_redirect_uri" 
                  type="text" 
                  readonly
                />
                <button @click="copyToClipboard(settings.google_redirect_uri)" class="copy-btn" title="Copy">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                  </svg>
                </button>
              </div>
              <span class="form-hint">Add this URL to your Google Cloud Console authorized redirect URIs</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="actions-bar">
        <div v-if="message" class="success-message">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
          {{ message }}
        </div>
        <div v-if="error" class="error-message">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          {{ error }}
        </div>
        <button @click="saveSettings" :disabled="saving" class="btn primary">
          <svg v-if="saving" class="spinner-small" viewBox="0 0 24 24" width="16" height="16">
            <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="3" stroke-dasharray="31.4 31.4" stroke-linecap="round"/>
          </svg>
          {{ saving ? 'Saving...' : 'Save Settings' }}
        </button>
      </div>

      <!-- Status Card -->
      <div class="card status-card">
        <h3>OAuth Status</h3>
        <div class="status-grid">
          <div class="status-item">
            <span class="status-label">Google Login</span>
            <span class="status-badge" :class="settings.google_client_id ? 'active' : 'inactive'">
              {{ settings.google_client_id ? 'Configured' : 'Not Configured' }}
            </span>
          </div>
          <div class="status-item">
            <span class="status-label">Enabled</span>
            <span class="status-badge" :class="settings.google_enabled === '1' ? 'active' : 'inactive'">
              {{ settings.google_enabled === '1' ? 'Yes' : 'No' }}
            </span>
          </div>
          <div class="status-item">
            <span class="status-label">Callback URL</span>
            <code>{{ settings.google_redirect_uri }}</code>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.page { padding: 0; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.page-title { font-size: 28px; font-weight: 700; color: #111827; }
.page-subtitle { color: #6b7280; margin-top: 4px; }
.loading { display: flex; justify-content: center; padding: 80px; }
.spinner { width: 48px; height: 48px; border: 4px solid #e5e7eb; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; }
.spinner-small { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.card { background: white; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px; margin-bottom: 24px; }

.instruction-card .card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; color: #3b82f6; }
.instruction-card h3 { font-size: 16px; font-weight: 600; color: #111827; margin: 0; }
.instruction-content ol { margin: 0; padding-left: 20px; color: #4b5563; line-height: 2; }
.instruction-content a { color: #3b82f6; text-decoration: underline; }
.instruction-content code { background: #f3f4f6; padding: 2px 8px; border-radius: 4px; font-size: 13px; }
.instruction-content strong { color: #111827; }

.providers-grid { display: grid; gap: 24px; }

.provider-card { }
.provider-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid #f3f4f6; }
.provider-info { display: flex; align-items: center; gap: 16px; }
.provider-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; background: #f9fafb; border-radius: 12px; }
.provider-info h3 { font-size: 18px; font-weight: 600; color: #111827; margin: 0 0 4px 0; }
.provider-info p { color: #6b7280; font-size: 14px; margin: 0; }

.toggle { position: relative; display: inline-block; width: 52px; height: 28px; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #e5e7eb; border-radius: 28px; transition: 0.3s; }
.toggle-slider:before { position: absolute; content: ""; height: 22px; width: 22px; left: 3px; bottom: 3px; background-color: white; border-radius: 50%; transition: 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.toggle input:checked + .toggle-slider { background-color: #3b82f6; }
.toggle input:checked + .toggle-slider:before { transform: translateX(24px); }

.provider-form { }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-weight: 500; margin-bottom: 8px; color: #374151; font-size: 14px; }
.input-group { display: flex; gap: 8px; }
.input-group input { flex: 1; padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; }
.input-group input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.copy-btn { padding: 12px; background: #f3f4f6; border: none; border-radius: 10px; cursor: pointer; color: #6b7280; transition: 0.2s; }
.copy-btn:hover { background: #e5e7eb; color: #374151; }
.form-hint { display: block; margin-top: 6px; font-size: 12px; color: #6b7280; }

.actions-bar { display: flex; justify-content: flex-end; gap: 16px; align-items: center; margin-bottom: 24px; }
.btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; font-size: 14px; font-weight: 600; border-radius: 10px; border: none; cursor: pointer; }
.btn.primary { background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.success-message { display: flex; align-items: center; gap: 8px; color: #10b981; font-size: 14px; }
.error-message { display: flex; align-items: center; gap: 8px; color: #ef4444; font-size: 14px; }

.status-card h3 { font-size: 16px; font-weight: 600; color: #111827; margin: 0 0 16px 0; }
.status-grid { display: grid; gap: 12px; }
.status-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f9fafb; border-radius: 8px; }
.status-label { font-size: 14px; color: #4b5563; }
.status-badge { padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: 600; }
.status-badge.active { background: #dcfce7; color: #16a34a; }
.status-badge.inactive { background: #fef2f2; color: #dc2626; }
.status-item code { font-size: 12px; color: #6b7280; background: white; padding: 4px 8px; border-radius: 4px; }
</style>
