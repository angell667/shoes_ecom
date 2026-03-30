<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const loading = ref(true)
const saving = ref(false)
const testingStripe = ref(false)
const testingPayPal = ref(false)
const message = ref({ type: '', text: '' })

const settings = ref({
  stripe_enabled: '0',
  stripe_key: '',
  stripe_secret: '',
  stripe_webhook_secret: '',
  paypal_enabled: '0',
  paypal_client_id: '',
  paypal_client_secret: '',
  paypal_mode: 'sandbox',
  cod_enabled: '1'
})

const showStripeSecret = ref(false)
const showPaypalSecret = ref(false)
const showStripeKey = ref(false)
const showPaypalClientId = ref(false)

const fetchSettings = async () => {
  try {
    const response = await axios.get('/api/admin/settings/payments')
    settings.value = response.data
  } catch (error) {
    showMessage('error', 'Failed to load payment settings')
  } finally {
    loading.value = false
  }
}

const saveSettings = async () => {
  saving.value = true
  message.value = { type: '', text: '' }
  
  try {
    const response = await axios.put('/api/admin/settings/payments', settings.value)
    showMessage('success', response.data.message)
  } catch (error) {
    showMessage('error', error.response?.data?.message || 'Failed to save settings')
  } finally {
    saving.value = false
  }
}

const toggleMethod = async (method) => {
  const enabled = settings.value[`${method}_enabled`] === '1'
  const newValue = enabled ? '0' : '1'
  
  try {
    const response = await axios.post('/api/admin/settings/payments/toggle', {
      method: method,
      enabled: !enabled
    })
    
    settings.value[`${method}_enabled`] = newValue ? '1' : '0'
    showMessage('success', response.data.message)
  } catch (error) {
    showMessage('error', error.response?.data?.message || 'Failed to toggle payment method')
  }
}

const testStripeConnection = async () => {
  testingStripe.value = true
  message.value = { type: '', text: '' }
  
  try {
    const response = await axios.post('/api/admin/settings/payments/test-stripe')
    showMessage('success', response.data.message)
  } catch (error) {
    showMessage('error', error.response?.data?.message || 'Stripe connection failed')
  } finally {
    testingStripe.value = false
  }
}

const testPayPalConnection = async () => {
  testingPayPal.value = true
  message.value = { type: '', text: '' }
  
  try {
    const response = await axios.post('/api/admin/settings/payments/test-paypal')
    showMessage('success', response.data.message)
  } catch (error) {
    showMessage('error', error.response?.data?.message || 'PayPal connection failed')
  } finally {
    testingPayPal.value = false
  }
}

const showMessage = (type, text) => {
  message.value = { type, text }
  setTimeout(() => {
    message.value = { type: '', text: '' }
  }, 5000)
}

const stripeEnabled = computed(() => settings.value.stripe_enabled === '1')
const paypalEnabled = computed(() => settings.value.paypal_enabled === '1')
const codEnabled = computed(() => settings.value.cod_enabled === '1')

onMounted(fetchSettings)
</script>

<template>
  <div class="payment-settings-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Payment Settings</h1>
        <p class="page-subtitle">Configure payment methods and credentials</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading settings...</p>
    </div>

    <template v-else>
      <!-- Message -->
      <div v-if="message.text" :class="['message', message.type]">
        {{ message.text }}
      </div>

      <div class="settings-grid">
        <!-- Stripe Settings -->
        <div class="settings-card" :class="{ active: stripeEnabled }">
          <div class="card-header">
            <div class="card-title-row">
              <div class="card-icon stripe">💳</div>
              <div>
                <h2>Stripe</h2>
                <p>Credit/Debit Card Payments</p>
              </div>
            </div>
            <label class="toggle-switch">
              <input 
                type="checkbox" 
                :checked="stripeEnabled"
                @change="toggleMethod('stripe')"
              />
              <span class="toggle-slider"></span>
            </label>
          </div>
          
          <div class="card-body">
            <div class="form-group">
              <label>
                Publishable Key
                <span class="badge" :class="stripeEnabled ? 'active' : 'inactive'">
                  {{ stripeEnabled ? 'Active' : 'Inactive' }}
                </span>
              </label>
              <div class="input-group">
                <input 
                  :type="showStripeKey ? 'text' : 'password'"
                  v-model="settings.stripe_key"
                  placeholder="pk_test_xxx"
                  :disabled="stripeEnabled"
                />
                <button @click="showStripeKey = !showStripeKey" class="btn-toggle-vis">
                  {{ showStripeKey ? '👁️' : '👁️‍🗨️' }}
                </button>
              </div>
            </div>
            
            <div class="form-group">
              <label>Secret Key</label>
              <div class="input-group">
                <input 
                  :type="showStripeSecret ? 'text' : 'password'"
                  v-model="settings.stripe_secret"
                  placeholder="sk_test_xxx"
                  :disabled="stripeEnabled"
                />
                <button @click="showStripeSecret = !showStripeSecret" class="btn-toggle-vis">
                  {{ showStripeSecret ? '👁️' : '👁️‍🗨️' }}
                </button>
              </div>
            </div>
            
            <div class="form-group">
              <label>Webhook Secret</label>
              <div class="input-group">
                <input 
                  type="text"
                  v-model="settings.stripe_webhook_secret"
                  placeholder="whsec_xxx"
                  :disabled="stripeEnabled"
                />
              </div>
            </div>
            
            <button 
              @click="testStripeConnection"
              :disabled="testingStripe || !settings.stripe_key || !settings.stripe_secret"
              class="btn-test"
            >
              {{ testingStripe ? 'Testing...' : 'Test Connection' }}
            </button>
          </div>
        </div>

        <!-- PayPal Settings -->
        <div class="settings-card" :class="{ active: paypalEnabled }">
          <div class="card-header">
            <div class="card-title-row">
              <div class="card-icon paypal">🅿️</div>
              <div>
                <h2>PayPal</h2>
                <p>PayPal Account Payments</p>
              </div>
            </div>
            <label class="toggle-switch">
              <input 
                type="checkbox" 
                :checked="paypalEnabled"
                @change="toggleMethod('paypal')"
              />
              <span class="toggle-slider"></span>
            </label>
          </div>
          
          <div class="card-body">
            <div class="form-group">
              <label>Mode</label>
              <select v-model="settings.paypal_mode" :disabled="paypalEnabled">
                <option value="sandbox">Sandbox (Testing)</option>
                <option value="live">Live (Production)</option>
              </select>
            </div>
            
            <div class="form-group">
              <label>
                Client ID
                <span class="badge" :class="paypalEnabled ? 'active' : 'inactive'">
                  {{ paypalEnabled ? 'Active' : 'Inactive' }}
                </span>
              </label>
              <div class="input-group">
                <input 
                  :type="showPaypalClientId ? 'text' : 'password'"
                  v-model="settings.paypal_client_id"
                  placeholder="PayPal Client ID"
                  :disabled="paypalEnabled"
                />
                <button @click="showPaypalClientId = !showPaypalClientId" class="btn-toggle-vis">
                  {{ showPaypalClientId ? '👁️' : '👁️‍🗨️' }}
                </button>
              </div>
            </div>
            
            <div class="form-group">
              <label>Client Secret</label>
              <div class="input-group">
                <input 
                  :type="showPaypalSecret ? 'text' : 'password'"
                  v-model="settings.paypal_client_secret"
                  placeholder="PayPal Client Secret"
                  :disabled="paypalEnabled"
                />
                <button @click="showPaypalSecret = !showPaypalSecret" class="btn-toggle-vis">
                  {{ showPaypalSecret ? '👁️' : '👁️‍🗨️' }}
                </button>
              </div>
            </div>
            
            <button 
              @click="testPayPalConnection"
              :disabled="testingPayPal || !settings.paypal_client_id || !settings.paypal_client_secret"
              class="btn-test"
            >
              {{ testingPayPal ? 'Testing...' : 'Test Connection' }}
            </button>
          </div>
        </div>

        <!-- Cash on Delivery -->
        <div class="settings-card cod" :class="{ active: codEnabled }">
          <div class="card-header">
            <div class="card-title-row">
              <div class="card-icon cod">💵</div>
              <div>
                <h2>Cash on Delivery</h2>
                <p>Pay when delivered</p>
              </div>
            </div>
            <label class="toggle-switch">
              <input 
                type="checkbox" 
                :checked="codEnabled"
                @change="toggleMethod('cod')"
              />
              <span class="toggle-slider"></span>
            </label>
          </div>
          
          <div class="card-body">
            <div class="cod-info">
              <p>When enabled, customers can choose to pay with cash when their order is delivered.</p>
              <ul>
                <li>No configuration required</li>
                <li>No payment gateway needed</li>
                <li>You handle collection manually</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="save-section">
        <button 
          @click="saveSettings"
          :disabled="saving"
          class="btn-save"
        >
          {{ saving ? 'Saving...' : 'Save All Settings' }}
        </button>
      </div>

      <!-- Info Section -->
      <div class="info-section">
        <h3>How to get credentials</h3>
        <div class="info-cards">
          <div class="info-card">
            <h4>Stripe</h4>
            <ol>
              <li>Go to <a href="https://dashboard.stripe.com" target="_blank">Stripe Dashboard</a></li>
              <li>Navigate to Developers → API Keys</li>
              <li>Copy your Publishable and Secret keys</li>
              <li>For webhooks, go to Developers → Webhooks</li>
            </ol>
          </div>
          <div class="info-card">
            <h4>PayPal</h4>
            <ol>
              <li>Go to <a href="https://developer.paypal.com" target="_blank">PayPal Developer</a></li>
              <li>Create or select an app</li>
              <li>Copy the Client ID and Secret</li>
              <li>Use Sandbox for testing, Live for production</li>
            </ol>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.payment-settings-page {
  padding: 20px;
}

.page-header {
  margin-bottom: 24px;
}

.page-title {
  font-size: 24px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.page-subtitle {
  color: #6b7280;
  margin: 4px 0 0 0;
}

.message {
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-weight: 500;
}

.message.success {
  background: #dcfce7;
  color: #16a34a;
  border: 1px solid #86efac;
}

.message.error {
  background: #fee2e2;
  color: #dc2626;
  border: 1px solid #fca5a5;
}

.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 24px;
  margin-bottom: 24px;
}

.settings-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  border: 2px solid transparent;
  transition: border-color 0.3s;
}

.settings-card.active {
  border-color: #16a34a;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.card-title-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.card-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
}

.card-icon.stripe { background: #e0e7ff; }
.card-icon.paypal { background: #dbeafe; }
.card-icon.cod { background: #dcfce7; }

.card-header h2 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
}

.card-header p {
  margin: 2px 0 0 0;
  font-size: 13px;
  color: #6b7280;
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 52px;
  height: 28px;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #e5e7eb;
  transition: 0.3s;
  border-radius: 28px;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 22px;
  width: 22px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.3s;
  border-radius: 50%;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

input:checked + .toggle-slider {
  background-color: #16a34a;
}

input:checked + .toggle-slider:before {
  transform: translateX(24px);
}

.card-body {
  padding: 20px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 6px;
  font-size: 14px;
}

.badge {
  padding: 2px 8px;
  border-radius: 9999px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
}

.badge.active {
  background: #dcfce7;
  color: #16a34a;
}

.badge.inactive {
  background: #f3f4f6;
  color: #6b7280;
}

.input-group {
  display: flex;
  gap: 8px;
}

.input-group input,
.form-group select {
  flex: 1;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  background: white;
}

.input-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #111827;
  box-shadow: 0 0 0 3px rgba(17, 24, 39, 0.1);
}

.input-group input:disabled,
.form-group select:disabled {
  background: #f3f4f6;
  cursor: not-allowed;
}

.btn-toggle-vis {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-toggle-vis:hover {
  background: #f3f4f6;
}

.btn-test {
  width: 100%;
  padding: 10px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  color: #374151;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-test:hover:not(:disabled) {
  background: #f3f4f6;
}

.btn-test:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.cod-info {
  color: #6b7280;
}

.cod-info p {
  margin: 0 0 12px 0;
}

.cod-info ul {
  margin: 0;
  padding-left: 20px;
}

.cod-info li {
  margin-bottom: 4px;
}

.save-section {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 32px;
}

.btn-save {
  padding: 12px 32px;
  background: #111827;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-save:hover:not(:disabled) {
  background: #374151;
}

.btn-save:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.info-section {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.info-section h3 {
  margin: 0 0 16px 0;
  font-size: 18px;
  font-weight: 600;
}

.info-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
}

.info-card {
  background: #f9fafb;
  border-radius: 12px;
  padding: 16px;
}

.info-card h4 {
  margin: 0 0 12px 0;
  font-size: 16px;
  font-weight: 600;
}

.info-card ol {
  margin: 0;
  padding-left: 20px;
  color: #6b7280;
  font-size: 14px;
}

.info-card li {
  margin-bottom: 8px;
}

.info-card a {
  color: #2563eb;
  text-decoration: none;
}

.info-card a:hover {
  text-decoration: underline;
}

.loading-container {
  text-align: center;
  padding: 60px 20px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e5e7eb;
  border-top-color: #111827;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>