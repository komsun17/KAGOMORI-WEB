<template>
  <div class="container">
    <div class="header">
      <div class="header-left">
        <img :src="logo" alt="Company Logo" class="logo" />
        <div class="title">Kagomori Status</div>
      </div>
      <h1>Thai Sinto <span class="sub">(Fabrication)</span></h1>
    </div>
    
    <!-- Error Display -->
    <div v-if="error" class="error-banner">
      <div class="error-message">
        <span>❌ {{ error }}</span>
        <button @click="retryFetch" class="retry-btn">Retry</button>
      </div>
    </div>

    <!-- Loading Indicator -->
    <div v-if="loading && !error" class="loading-banner">
      <span>🔄 Loading data...</span>
    </div>

    <table class="main-table">
      <thead>
        <tr>
          <th class="col-no">#</th>
          <th class="col-kago-no">KAGO No.</th>
          <th class="col-time">ST</th>
          <th class="col-time">AT(Hr)</th>
          <th class="col-diff">Diff</th>
          <th class="col-datetime">PlanStart</th>
          <th class="col-datetime">PlanFinish</th>
          <th class="col-project">Project</th>
          <th class="col-worker">WorkBy</th>
          <th class="col-status">Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, index) in paginatedData" :key="index" 
            :class="{ 'overtime-row': isOvertime(row) }">
          <td>{{ (currentPage - 1) * pageSize + index + 1 }}</td>
          <td>{{ row['Prod_ Order No_'] }}</td>
          <td>{{ formatTime(row['ST.HR']) }}</td>
          <td>{{ formatActualTime(row['AT.HR']) }}</td>
          <td>
            <span class="diff-value" :class="getDiffClass(row)">
              {{ formatDiff(row) }}
            </span>
          </td>
          <td>{{ row['Plan Start'] }}</td>
          <td>{{ row['Plan Finish'] }}</td>
          <td>{{ row['Project'] }}</td>
          <td>{{ row['Work By'] }}</td>
          <td><span class="status" :class="{ 'status-overtime': isOvertime(row) }">In-Process</span></td>
        </tr>
        <!-- Show message when no data -->
        <tr v-if="!loading && !error && timesheetData.length === 0">
          <td colspan="10" style="text-align: center; padding: 40px;">
            No data available
          </td>
        </tr>
      </tbody>
    </table>
    <div class="footer">
      <div class="footer-box">{{ currentDay }}</div>
      <div class="footer-box">{{ currentTime }}</div>
      <div class="footer-box">{{ currentDate }}</div>
      <div class="footer-box">Page {{ currentPage }} of {{ totalPages }}</div>
      <!-- Network Status Indicator -->
      <div class="footer-box" :class="{ 'error-status': error, 'loading-status': loading }">
        {{ networkStatus }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import logo from './assets/logo.png'

const timesheetData = ref([])
const currentDate = ref('')
const currentTime = ref('')
const currentDay = ref('')
const currentPage = ref(1)
const pageSize = 12

// Error handling states
const loading = ref(false)
const error = ref('')
const retryCount = ref(0)
const maxRetries = 3

const totalPages = computed(() => {
  return Math.ceil(timesheetData.value.length / pageSize) || 1
})

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return timesheetData.value.slice(start, start + pageSize)
})

const networkStatus = computed(() => {
  if (loading.value) return 'Loading...'
  if (error.value) return 'Offline'
  return 'Online'
})

function formatTime(mins) {
  const minutes = parseInt(mins)
  if (isNaN(minutes)) return '00:00'
  const hrs = Math.floor(minutes / 60)
  const remMins = minutes % 60
  return `${hrs.toString().padStart(2, '0')}:${remMins.toString().padStart(2, '0')}`
}

function formatActualTime(timeValue) {
  // Handle different time formats from the API
  if (!timeValue) return '00:00'
  
  // If it's already in HH:MM:SS format
  if (typeof timeValue === 'string' && timeValue.includes(':')) {
    return timeValue.substring(0, 5) // Return HH:MM only
  }
  
  // If it's in minutes
  const minutes = parseFloat(timeValue)
  if (!isNaN(minutes)) {
    const hrs = Math.floor(minutes / 60)
    const mins = Math.floor(minutes % 60)
    return `${hrs.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`
  }
  
  return timeValue
}

function calculateDiffMinutes(row) {
  // Convert ST.HR to minutes
  const stMinutes = parseFloat(row['ST.HR']) || 0
  
  // Convert AT.HR to minutes
  let atMinutes = 0
  const atValue = row['AT.HR']
  
  if (typeof atValue === 'string' && atValue.includes(':')) {
    // Parse HH:MM:SS format
    const timeParts = atValue.split(':')
    atMinutes = (parseInt(timeParts[0]) * 60) + parseInt(timeParts[1])
  } else {
    atMinutes = parseFloat(atValue) || 0
  }
  
  // Return difference (AT.HR - ST.HR)
  return atMinutes - stMinutes
}

function formatDiff(row) {
  const diffMinutes = calculateDiffMinutes(row)
  const sign = diffMinutes >= 0 ? '+' : ''
  const absDiff = Math.abs(diffMinutes)
  const hrs = Math.floor(absDiff / 60)
  const mins = Math.floor(absDiff % 60)
  
  return `${sign}${hrs.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`
}

function getDiffClass(row) {
  const diffMinutes = calculateDiffMinutes(row)
  if (diffMinutes > 0) {
    return 'diff-positive' // Over time
  } else if (diffMinutes < 0) {
    return 'diff-negative' // Under time
  } else {
    return 'diff-zero' // Exact time
  }
}

function isOvertime(row) {
  return calculateDiffMinutes(row) > 0
}

function updateTime() {
  const now = new Date()
  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
  currentDay.value = days[now.getDay()]
  currentTime.value = now.toLocaleTimeString()
  currentDate.value = now.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  })
}

// Logo error handling functions


async function fetchData() {
  loading.value = true
  error.value = ''
  
  try {
    // Use the existing Docker Apache server
    const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8080/kagomori/backend/'
    
    // Create AbortController for timeout (compatible with older browsers)
    const controller = new AbortController()
    const timeoutId = setTimeout(() => controller.abort(), 10000) // 10 second timeout
    
    const response = await fetch(apiUrl + 'get_timesheet.php', {
      method: 'GET',
      mode: 'cors', // added
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      signal: controller.signal
    })

    clearTimeout(timeoutId) // Clear timeout if request succeeds

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`)
    }

    // Check if response is JSON
    const contentType = response.headers.get('content-type')
    if (!contentType || !contentType.includes('application/json')) {
      throw new Error('Server returned non-JSON response (possibly HTML error page)')
    }

    const data = await response.json()
    
    // Check if response contains error
    if (data.error) {
      throw new Error(data.message || 'Server returned error response')
    }
    
    timesheetData.value = data
    retryCount.value = 0 // Reset retry count on success
    
  } catch (err) {
    console.error('Fetch error:', err)
    
    if (err.name === 'AbortError') {
      error.value = 'Request timeout - Please check your connection'
    } else if (err.message.includes('Failed to fetch') || err.message.includes('ERR_CONNECTION_REFUSED')) {
      error.value = 'Backend server is not running - Using sample data'
      // Load mock data when backend is unavailable
    } else if (err.message.includes('non-JSON response')) {
      error.value = 'Server configuration error - Check backend setup'
    } else if (err.message.includes('HTTP')) {
      error.value = `Server error: ${err.message}`
    } else {
      error.value = 'An unexpected error occurred'
    }

    // Auto retry with exponential backoff (but not for connection refused)
    if (retryCount.value < maxRetries && !err.message.includes('ERR_CONNECTION_REFUSED') && !err.message.includes('Failed to fetch')) {
      retryCount.value++
      const delay = Math.pow(2, retryCount.value) * 1000 // 2s, 4s, 8s
      setTimeout(() => {
        fetchData()
      }, delay)
    } else if (err.message.includes('ERR_CONNECTION_REFUSED') || err.message.includes('Failed to fetch')) {
      // For connection refused, don't retry automatically but still show data
      console.log('Backend unavailable, using mock data')
    }
  } finally {
    loading.value = false
  }
}

function retryFetch() {
  retryCount.value = 0
  error.value = '' // Clear error to attempt real connection
  fetchData()
}

function nextPage() {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  } else {
    currentPage.value = 1
  }
}



let autoPageInterval
let refreshInterval
let clockInterval

onMounted(() => {
  fetchData()
  updateTime()

  // Auto เปลี่ยนหน้า
  autoPageInterval = setInterval(() => {
    nextPage()
  }, 10000)

  // Refresh ข้อมูลทุก 15 วิ (only if no connection error)
  refreshInterval = setInterval(() => {
    if (!loading.value && !error.value.includes('Backend server is not running')) {
      fetchData()
    }
  }, 15000)

  // อัปเดตนาฬิกา
  clockInterval = setInterval(updateTime, 1000)
})

onUnmounted(() => {
  clearInterval(autoPageInterval)
  clearInterval(refreshInterval)
  clearInterval(clockInterval)
})
</script>


<style>
/* Global styles ที่กระทบทั้งหน้าเว็บ */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  /*outline: 1px solid red;*/
}

body {
  margin: 0;
  padding: 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #000;
  color: #fff;
  overflow-x: hidden;
  overflow-y: hidden;
}

#app {
  /* ใส่ CSS ที่ต้องการ */
  max-width: 0; 
  margin: 0;
  min-height: 100vh;
  padding: 0 0;
  /* หรืออื่น ๆ ตามต้องการ */
}

.logo {
  margin-left: 16px; /* เพิ่มระยะห่างด้านซ้าย */
  height: 1.5em;
  margin-right: 12px;
  vertical-align: middle;
}
</style>

<style scoped>

.container {
  width: 100vw;
  height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  padding: 0;
  box-sizing: border-box;
  overflow-x: hidden;
}

.header {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 10px 0; /* ตัด padding ซ้าย-ขวา */
  margin: 0;
}

.header-left {
  display: flex;
  align-items: center;
}

.logo {
  height: 6em; /* เท่ากับขนาดฟอนต์ของ .app-title */
  margin-right: 12px;
  vertical-align: middle;
}

.title {
  font-size: 2.2rem;
  font-weight: bold;
  color: #fff;
  background-color: #d65d0e;
  padding: 10px 20px;
  margin-left: 0;
}

.sub {
  color: #fff;
  font-weight: 300;
}

h1 {
  margin: 10px 0 20px 0;
  font-size: 4rem;
  padding-left: 30px;
}

.main-table {
  width: 100%;
  margin: 0;
  border-collapse: collapse;
  background-color: #2f5483;
  font-size: 1.6rem;
  table-layout: fixed;
}

th, td {
  padding: 12px 18px;
  text-align: left;
  color: #fff;
  border-bottom: 1px solid #444;
  height: 60px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

th {
  background-color: #102e4a;
  font-weight: bold;
  font-size: 1.7rem;
}

.col-no { width: 3%; }
.col-kago-no { width: 8%; }
.col-time { width: 5%; }
.col-diff { width: 5%; }
.col-datetime { width: 8%; }
.col-project { width: 12%; }
.col-worker { width: 9%; }
.col-status { width: 9%; }

.status {
  background-color: orange;
  color: white;
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: bold;
}

/* Overtime row styling */
.overtime-row {
  background-color: rgba(220, 53, 69, 0.2) !important;
  border-left: 4px solid #dc3545;
}

.overtime-row td {
  background-color: rgba(220, 53, 69, 0.1);
  color: #ffebee;
}

.overtime-row:hover {
  background-color: rgba(220, 53, 69, 0.3) !important;
}

.status-overtime {
  background-color: #dc3545 !important;
  color: white;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
  100% {
    opacity: 1;
  }
}

.footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  margin-top: auto;
  font-size: 1.4rem;
  /* background-color: #c14c1c; */
  padding: 10px;
}

.footer-box {
  padding: 6px 12px;
  background-color: #c14c1c;
  color: #fff;
  font-weight: bold;
  min-width: 125px;
  text-align: center;
}

/* Error and Loading Styles */
.error-banner {
  width: 100%;
  background-color: #dc3545;
  color: white;
  padding: 10px 20px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 1.2rem;
  font-weight: bold;
}

.error-message {
  display: flex;
  align-items: center;
  gap: 15px;
}

.retry-btn {
  background-color: #fff;
  color: #dc3545;
  border: none;
  padding: 5px 15px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
  transition: background-color 0.2s;
}

.retry-btn:hover {
  background-color: #f8f9fa;
}

.loading-banner {
  width: 100%;
  background-color: #007bff;
  color: white;
  padding: 10px 20px;
  text-align: center;
  font-size: 1.2rem;
  font-weight: bold;
}

.footer-box.error-status {
  background-color: #dc3545;
}

.footer-box.loading-status {
  background-color: #007bff;
}

/* Diff value styling */
.diff-value {
  font-weight: bold;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.9em;
}

.diff-positive {
  background-color: rgba(220, 53, 69, 0.2);
  color: #dc3545;
  border: 1px solid rgba(220, 53, 69, 0.3);
}

.diff-negative {
  background-color: rgba(40, 167, 69, 0.2);
  color: #28a745;
  border: 1px solid rgba(40, 167, 69, 0.3);
}

.diff-zero {
  background-color: rgba(108, 117, 125, 0.2);
  color: #6c757d;
  border: 1px solid rgba(108, 117, 125, 0.3);
}
</style>
