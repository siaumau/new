<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import PrintTemplateModal from '../components/PrintTemplateModal.vue';

const { t } = useI18n();

// 響應式資料
const locations = ref([]);
const loading = ref(false);
const searchText = ref('');
const selectedBuilding = ref('');
const selectedCategory = ref('');
const selectedDateRange = ref('');
const customDate = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const showEditModal = ref(false);
const showQRModal = ref(false);
const showDetailsModal = ref(false);
const selectedLocation = ref(null);
const qrCodeUrl = ref('');
const error = ref('');
const locationItems = ref([]);
const floorDistribution = ref([]);

// 批次列印相關狀態
const selectedLocations = ref(new Set());
const showBatchPrintMode = ref(false);
const batchPrintLoading = ref(false);
const showPrintTemplateModal = ref(false);

// 篩選後的位置資料
const filteredLocations = computed(() => {
  let filtered = locations.value;

  // 搜尋篩選
  if (searchText.value) {
    filtered = filtered.filter(location =>
      location.code.toLowerCase().includes(searchText.value.toLowerCase()) ||
      location.name.toLowerCase().includes(searchText.value.toLowerCase())
    );
  }

  // 建築篩選
  if (selectedBuilding.value) {
    filtered = filtered.filter(location => location.building === selectedBuilding.value);
  }

  // 類別篩選
  if (selectedCategory.value) {
    filtered = filtered.filter(location => location.storageType === selectedCategory.value);
  }

  // 日期範圍篩選
  if (selectedDateRange.value) {
    const today = new Date();
    const filterDate = new Date();
    
    switch (selectedDateRange.value) {
      case 'today':
        filterDate.setHours(0, 0, 0, 0);
        filtered = filtered.filter(location => {
          const locationDate = new Date(location.createdAt);
          return locationDate >= filterDate;
        });
        break;
      case 'yesterday':
        filterDate.setDate(filterDate.getDate() - 1);
        filterDate.setHours(0, 0, 0, 0);
        const yesterdayEnd = new Date(filterDate);
        yesterdayEnd.setDate(yesterdayEnd.getDate() + 1);
        filtered = filtered.filter(location => {
          const locationDate = new Date(location.createdAt);
          return locationDate >= filterDate && locationDate < yesterdayEnd;
        });
        break;
      case 'thisWeek':
        const startOfWeek = new Date(today);
        startOfWeek.setDate(today.getDate() - today.getDay());
        startOfWeek.setHours(0, 0, 0, 0);
        filtered = filtered.filter(location => {
          const locationDate = new Date(location.createdAt);
          return locationDate >= startOfWeek;
        });
        break;
      case 'lastWeek':
        const lastWeekStart = new Date(today);
        lastWeekStart.setDate(today.getDate() - today.getDay() - 7);
        lastWeekStart.setHours(0, 0, 0, 0);
        const lastWeekEnd = new Date(lastWeekStart);
        lastWeekEnd.setDate(lastWeekEnd.getDate() + 7);
        filtered = filtered.filter(location => {
          const locationDate = new Date(location.createdAt);
          return locationDate >= lastWeekStart && locationDate < lastWeekEnd;
        });
        break;
      case 'thisMonth':
        const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        filtered = filtered.filter(location => {
          const locationDate = new Date(location.createdAt);
          return locationDate >= startOfMonth;
        });
        break;
      case 'lastMonth':
        const lastMonthStart = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 1);
        filtered = filtered.filter(location => {
          const locationDate = new Date(location.createdAt);
          return locationDate >= lastMonthStart && locationDate < lastMonthEnd;
        });
        break;
    }
  }

  // 自訂日期篩選
  if (customDate.value) {
    const searchDate = new Date(customDate.value);
    searchDate.setHours(0, 0, 0, 0);
    const searchDateEnd = new Date(searchDate);
    searchDateEnd.setDate(searchDateEnd.getDate() + 1);
    
    filtered = filtered.filter(location => {
      const locationDate = new Date(location.createdAt);
      return locationDate >= searchDate && locationDate < searchDateEnd;
    });
  }

  return filtered;
});

// 分頁資料
const paginatedLocations = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredLocations.value.slice(start, end);
});

// 總頁數
const totalPages = computed(() => {
  return Math.ceil(filteredLocations.value.length / itemsPerPage.value);
});

// 資料轉換函數：將 API 回應轉換為前端格式
const transformApiDataToFrontend = (apiData) => {
  return {
    id: apiData.id,
    code: apiData.location_code,
    name: apiData.location_name,
    building: apiData.building_code,
    storageType: apiData.storage_type_code,
    storageCode: apiData.position_code,
    capacity: apiData.capacity,
    stock: apiData.current_stock,
    utilization: apiData.capacity > 0 ? Math.round((apiData.current_stock / apiData.capacity) * 100) : 0,
    notes: apiData.notes,
    enabled: apiData.is_active === 1,
    qrData: apiData.qr_code_data || apiData.location_code,
    // 額外的 API 欄位
    floorNumber: apiData.floor_number,
    floorAreaCode: apiData.floor_area_code,
    subAreaCode: apiData.sub_area_code,
    createdAt: apiData.created_at,
    updatedAt: apiData.updated_at
  };
};

// 資料轉換函數：將前端格式轉換為 API 格式
const transformFrontendDataToApi = (frontendData) => {
  return {
    id: frontendData.id,
    location_code: frontendData.code,
    location_name: frontendData.name,
    building_code: frontendData.building,
    floor_number: frontendData.floorNumber || "1",
    floor_area_code: frontendData.floorAreaCode || "01",
    storage_type_code: frontendData.storageType,
    sub_area_code: frontendData.subAreaCode || "01",
    position_code: frontendData.storageCode,
    capacity: frontendData.capacity,
    current_stock: frontendData.stock,
    qr_code_data: frontendData.qrData,
    notes: frontendData.notes,
    is_active: frontendData.enabled ? 1 : 0
  };
};

// 使用相對路徑，讓 Vite 代理處理 API 請求

// 載入位置資料
const loadLocations = async () => {
  loading.value = true;
  error.value = '';
  try {
    const response = await fetch(`/api/v1/locations`, {
      method: 'GET',
      headers: {
        'accept': '*/*',
        'X-CSRF-TOKEN': ''
      }
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    const apiData = data.data || data;

    // 轉換 API 資料格式到前端格式
    locations.value = Array.isArray(apiData)
      ? apiData.map(transformApiDataToFrontend)
      : [];
  } catch (err) {
    console.error('載入位置資料失敗:', err);
    error.value = err.message || '載入位置資料失敗';
  } finally {
    loading.value = false;
  }
};

// 搜尋功能
const handleSearch = () => {
  currentPage.value = 1;
};

// 載入位置詳細資訊
const loadLocationDetails = async (locationId) => {
  try {
    // 載入位置商品清單
    const itemsResponse = await fetch(`/api/v1/locations/${locationId}/items`, {
      method: 'GET',
      headers: {
        'accept': '*/*',
        'X-CSRF-TOKEN': ''
      }
    });

    if (itemsResponse.ok) {
      const itemsData = await itemsResponse.json();
      locationItems.value = itemsData.items || [];
    } else {
      locationItems.value = [];
    }

    // 載入層架分布資料（只有storage_type_code是'Shelf'時才載入）
    const currentLocation = selectedLocation.value;
    if (currentLocation && currentLocation.storageType === 'Shelf') {
      const floorResponse = await fetch(`/api/v1/locations/${locationId}/floor-distribution`, {
        method: 'GET',
        headers: {
          'accept': '*/*',
          'X-CSRF-TOKEN': ''
        }
      });

      if (floorResponse.ok) {
        const floorData = await floorResponse.json();
        floorDistribution.value = floorData.floor_distribution || [];
      } else {
        floorDistribution.value = [];
      }
    } else {
      // 如果不是層架類型，清空層架分布資料
      floorDistribution.value = [];
    }
  } catch (err) {
    console.error('載入位置詳細資訊失敗:', err);
    locationItems.value = [];
    floorDistribution.value = [];
  }
};

// 顯示詳細資訊
const showDetails = async (location) => {
  selectedLocation.value = {
    ...location,
    // 確保所有必要的欄位都存在
    floorNumber: location.floorNumber || "1",
    floorAreaCode: location.floorAreaCode || "01",
    subAreaCode: location.subAreaCode || "01"
  };

  // 載入詳細資訊
  await loadLocationDetails(location.id);
  showDetailsModal.value = true;
};

// 轉換位置代碼的函數
const transformLocationCode = (code) => {
  if (!code) return '';
  
  // 檢查是否符合 XX-X-XXXX 格式
  const parts = code.split('-');
  if (parts.length >= 3) {
    const middlePart = parts[1];
    if (middlePart === 'A') {
      parts[1] = '棧板';
    } else if (middlePart === 'S') {
      parts[1] = '層架';
    }
    return parts.join('-');
  }
  
  return code;
};

// 顯示 QR Code
const showQRCode = (location) => {
  if (!location) {
    console.error('Location is null or undefined');
    alert('選擇的位置資料有誤，請重試');
    return;
  }
  
  selectedLocation.value = location;
  // 確保有 qrData 屬性，如果沒有則使用 code 作為替代
  const originalCode = location.qrData || location.code || location.location_code || '';
  const transformedCode = transformLocationCode(originalCode);
  qrCodeUrl.value = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(transformedCode)}`;
  showQRModal.value = true;
};

// 單個 QR Code 列印 - 顯示模板選擇
const showSinglePrintTemplate = (location) => {
  if (!location) {
    console.error('Location is null or undefined');
    alert('選擇的位置資料有誤，請重試');
    return;
  }
  
  selectedLocation.value = location;
  // 確保有 qrData 屬性，如果沒有則使用 code 作為替代
  const originalCode = location.qrData || location.code || location.location_code || '';
  const transformedCode = transformLocationCode(originalCode);
  qrCodeUrl.value = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(transformedCode)}`;
  showPrintTemplateModal.value = true;
};

// 處理從 Modal 中點擊列印
const handlePrintFromModal = () => {
  if (!selectedLocation.value) {
    alert('沒有選擇的位置資料');
    return;
  }
  
  // 保存當前選擇的位置，因為 closeModal 會清空它
  const locationToSave = selectedLocation.value;
  
  // 關閉當前 Modal
  closeModal();
  
  // 重新設置位置並顯示模板選擇
  selectedLocation.value = locationToSave;
  showPrintTemplateModal.value = true;
};

// 編輯位置
const editLocation = (location) => {
  selectedLocation.value = {
    ...location,
    // 確保所有必要的欄位都存在
    floorNumber: location.floorNumber || "1",
    floorAreaCode: location.floorAreaCode || "01",
    subAreaCode: location.subAreaCode || "01"
  };
  showEditModal.value = true;
};

// 刪除位置
const deleteLocation = async (location) => {
  if (confirm(`確定要刪除位置「${location.name}」嗎？`)) {
    try {
      const response = await fetch(`/api/v1/locations/${location.id}`, {
        method: 'DELETE',
        headers: {
          'accept': '*/*',
          'X-CSRF-TOKEN': ''
        }
      });

      if (response.ok) {
        alert('位置已成功刪除');
        await loadLocations();
      } else {
        const errorData = await response.json();
        if (response.status === 400) {
          alert(`刪除失敗：${errorData.message}\n櫃位上有 ${errorData.item_count} 個商品，請先移除商品後再刪除位置。`);
        } else {
          alert('刪除失敗: ' + (errorData.message || '未知錯誤'));
        }
      }
    } catch (error) {
      console.error('刪除位置失敗:', error);
      alert('刪除失敗: ' + error.message);
    }
  }
};

// 儲存位置
const saveLocation = async () => {
  if (!selectedLocation.value) return;

  try {
    const isEdit = selectedLocation.value.id;
    const url = isEdit
              ? `/api/v1/locations/${selectedLocation.value.id}`
        : `/api/v1/locations`;

    const method = isEdit ? 'PUT' : 'POST';

    // 轉換前端資料格式為 API 格式
    const apiData = transformFrontendDataToApi(selectedLocation.value);

    const response = await fetch(url, {
      method: method,
      headers: {
        'accept': '*/*',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      },
      body: JSON.stringify(apiData)
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    // 儲存成功後重新載入資料
    await loadLocations();
    showEditModal.value = false;
    selectedLocation.value = null;
  } catch (err) {
    console.error('儲存位置失敗:', err);
    alert('儲存位置失敗: ' + (err.message || '未知錯誤'));
  }
};

//

// 下載CSV模板
const downloadTemplate = () => {
  // CSV模板內容（三個必填欄位）
  const csvContent = [
    'building_code,storage_type_code,sub_area_code',
    'CH,Shelf,C201',
    'TP,Area,S101'
  ].join('\n');

  // 添加BOM以支援Excel正確顯示繁體中文
  const BOM = '\uFEFF';
  const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' });

  // 創建下載連結
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', 'location_template.csv');
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
};

// 處理CSV文件匯入
const handleFileImport = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // 檢查文件類型
  if (!file.name.toLowerCase().endsWith('.csv')) {
    alert('請選擇CSV格式的文件');
    return;
  }

  try {
    const text = await file.text();
    const lines = text.split('\n').filter(line => line.trim());

    if (lines.length < 2) {
      alert('CSV文件格式錯誤或沒有資料');
      return;
    }

    // 解析CSV數據（跳過標題行）
    const dataLines = lines.slice(1);
    const locations = [];

    for (let i = 0; i < dataLines.length; i++) {
      const line = dataLines[i].trim();
      if (!line) continue;

                  // 解析CSV行（處理可能包含逗號的欄位）
      const columns = parseCSVLine(line);

      if (columns.length < 3) {
        alert(`第 ${i + 2} 行資料格式錯誤，請檢查CSV格式（需要3個欄位：building_code, storage_type_code, sub_area_code）`);
        return;
      }

      // CSV 格式：building_code, storage_type_code, sub_area_code (三個必填欄位)
      const building_code = columns[0].trim();
      const storage_type_code = columns[1].trim();
      const sub_area_code = columns[2].trim();

      // 生成位置代碼和名稱
      const location_code = `${building_code}-${storage_type_code}-${sub_area_code}`;
      const location_name = `${building_code}-${storage_type_code}-${sub_area_code}`;

      const locationData = {
        location_code: location_code,
        location_name: location_name,
        building_code: building_code,
        floor_number: '1',
        floor_area_code: '01',
        storage_type_code: storage_type_code,
        sub_area_code: sub_area_code,
        position_code: sub_area_code || location_code, // 使用sub_area_code作為position_code，如果為空則使用location_code
        capacity: 100, // 預設容量
        current_stock: 0,
        qr_code_data: location_code,
        notes: null, // 不使用notes欄位
        is_active: true
      };

      console.log(`第 ${i + 1} 筆資料:`, locationData);

      locations.push(locationData);
    }

    // 批量匯入位置資料
    await importLocations(locations);

    // 清空文件輸入
    event.target.value = '';

  } catch (error) {
    console.error('匯入文件失敗:', error);
    alert('匯入文件失敗: ' + error.message);
  }
};

// 解析CSV行（處理包含逗號的欄位）
const parseCSVLine = (line) => {
  const result = [];
  let current = '';
  let inQuotes = false;

  for (let i = 0; i < line.length; i++) {
    const char = line[i];

    if (char === '"') {
      inQuotes = !inQuotes;
    } else if (char === ',' && !inQuotes) {
      result.push(current);
      current = '';
    } else {
      current += char;
    }
  }

  result.push(current);
  return result;
};

// 生成並下載錯誤報告
const generateAndDownloadErrorReport = async (errors) => {
  const timestamp = new Date().toISOString().replace(/:/g, '-').split('.')[0];
  const filename = `locations_import_errors_${timestamp}.txt`;
  
  let errorReport = `位置批量匯入錯誤報告\n`;
  errorReport += `生成時間: ${new Date().toLocaleString('zh-TW')}\n`;
  errorReport += `錯誤總數: ${errors.length}\n`;
  errorReport += `${'='.repeat(50)}\n\n`;
  
  errors.forEach((error, index) => {
    errorReport += `錯誤 ${index + 1}:\n`;
    errorReport += `  CSV行號: ${error.index + 1}\n`;
    errorReport += `  位置代碼: ${error.location_code || '未指定'}\n`;
    errorReport += `  錯誤內容: ${error.error}\n`;
    errorReport += `\n`;
  });
  
  errorReport += `${'='.repeat(50)}\n`;
  errorReport += `請檢查上述錯誤並修正CSV檔案後重新匯入。\n`;
  
  // 創建並下載文件
  const blob = new Blob([errorReport], { type: 'text/plain;charset=utf-8' });
  const url = window.URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  link.style.display = 'none';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  window.URL.revokeObjectURL(url);
};

// 批量匯入位置資料
const importLocations = async (locations) => {
  loading.value = true;

  try {
    console.log('發送請求到:', '/api/v1/locations/batch');
    console.log('請求資料:', { locations });

    const response = await fetch('/api/v1/locations/batch', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ locations })
    });

        console.log('回應狀態:', response.status);
    console.log('回應URL:', response.url);

    const result = await response.json();
    console.log('回應資料:', result);

    if (!response.ok) {
      console.error('API錯誤詳情:', result);
      
      // 處理 422 錯誤響應，顯示詳細錯誤信息
      if (response.status === 422 && result.errors) {
        // 檢查是否為數組格式的錯誤 (LocationController 的格式)
        if (Array.isArray(result.errors) && result.errors.length > 0) {
          await generateAndDownloadErrorReport(result.errors);
          
          // 顯示詳細錯誤信息
          let errorSummary = `匯入失敗，共發現 ${result.error_count} 個錯誤：\n\n`;
          result.errors.forEach((err, index) => {
            // CSV 第2行是第1筆資料，所以 err.index + 1
            errorSummary += `${index + 1}. 第${err.index + 1}行 (${err.location_code}): ${err.error}\n`;
          });
          errorSummary += `\n錯誤報告已自動下載到您的電腦`;
          
          throw new Error(errorSummary);
        }
        // 檢查是否為對象格式的錯誤 (Laravel 驗證錯誤格式)
        else if (typeof result.errors === 'object' && Object.keys(result.errors).length > 0) {
          const validationErrors = [];
          Object.keys(result.errors).forEach(key => {
            const fieldErrors = result.errors[key];
            if (Array.isArray(fieldErrors)) {
              fieldErrors.forEach(errorMsg => {
                // 從 key 中提取索引，例如 "locations.1.position_code" -> 索引 1
                const indexMatch = key.match(/locations\.(\d+)\./);
                const index = indexMatch ? parseInt(indexMatch[1]) : 0;
                const fieldName = key.replace(/^locations\.\d+\./, '');
                
                validationErrors.push({
                  index: index,
                  location_code: `第${index + 1}筆`,
                  error: `${fieldName} 不能為空`
                });
              });
            }
          });
          
          if (validationErrors.length > 0) {
            await generateAndDownloadErrorReport(validationErrors);
            
            let errorSummary = `匯入失敗，共發現 ${validationErrors.length} 個驗證錯誤：\n\n`;
            validationErrors.forEach((err, index) => {
              errorSummary += `${index + 1}. ${err.location_code} - ${err.error}\n`;
            });
            errorSummary += `\n錯誤報告已自動下載到您的電腦`;
            
            throw new Error(errorSummary);
          }
        }
      }
      
      throw new Error(`HTTP error! status: ${response.status}, message: ${result.message || '未知錯誤'}`);
    }

        if (response.ok) {
      const { created_count, error_count, errors } = result;

      // 顯示匯入結果
      let message = `匯入完成！\n成功: ${created_count} 筆\n失敗: ${error_count} 筆`;

      if (errors && errors.length > 0) {
        message += '\n\n錯誤詳情:\n';
        errors.slice(0, 5).forEach(error => {
          message += `位置代碼 ${error.location_code}: ${error.error}\n`;
        });
        if (errors.length > 5) {
          message += `... 還有 ${errors.length - 5} 個錯誤`;
        }
      }

      alert(message);

      // 重新載入位置資料
      if (created_count > 0) {
        await loadLocations();
      }
    } else {
      // 處理驗證錯誤
      if (result.errors) {
        // 檢查是否有重複的位置代碼
        const duplicateErrors = [];
        Object.keys(result.errors).forEach(key => {
          if (key.includes('location_code') && result.errors[key].some(msg => msg.includes('already been taken'))) {
            const index = key.match(/locations\.(\d+)\./)?.[1];
            if (index !== undefined) {
              duplicateErrors.push({
                index: parseInt(index),
                locationCode: locations[parseInt(index)]?.location_code || '未知'
              });
            }
          }
        });

        if (duplicateErrors.length > 0) {
          // 生成重複清單的txt文件
          const duplicateList = duplicateErrors.map(item =>
            `第 ${item.index + 1} 筆資料: ${item.locationCode} (已存在)`
          ).join('\n');

          const txtContent = `位置代碼重複清單\n生成時間: ${new Date().toLocaleString('zh-TW')}\n\n${duplicateList}`;

          // 下載txt文件
          const blob = new Blob(['\uFEFF' + txtContent], { type: 'text/plain;charset=utf-8;' });
          const link = document.createElement('a');
          const url = URL.createObjectURL(blob);
          link.setAttribute('href', url);
          link.setAttribute('download', `重複位置代碼清單_${new Date().toISOString().slice(0,10)}.txt`);
          link.style.visibility = 'hidden';
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          URL.revokeObjectURL(url);

          // 詢問用戶是否要跳過重複項目並繼續匯入
          const skipDuplicates = confirm(
            `發現 ${duplicateErrors.length} 個重複的位置代碼！\n\n` +
            `重複清單已下載為txt文件。\n\n` +
            `點擊「確定」跳過重複項目並匯入其他資料\n` +
            `點擊「取消」停止匯入`
          );

          if (skipDuplicates) {
            // 移除重複的項目，重新匯入
            const validLocations = locations.filter((_, index) =>
              !duplicateErrors.some(dup => dup.index === index)
            );

            if (validLocations.length > 0) {
              await importLocations(validLocations);
            } else {
              alert('所有位置代碼都重複，沒有資料可以匯入。');
            }
          }
        } else {
          // 其他驗證錯誤
          let errorMessage = '資料驗證失敗：\n';
          Object.keys(result.errors).forEach(key => {
            const fieldName = key.replace('locations.', '').replace('.location_code', '');
            errorMessage += `第 ${parseInt(fieldName) + 1} 筆資料: ${result.errors[key].join(', ')}\n`;
          });
          alert(errorMessage);
        }
      } else {
        alert('匯入失敗: ' + (result.message || '未知錯誤'));
      }
    }
  } catch (error) {
    console.error('匯入文件失敗:', error);
    alert('匯入文件失敗: ' + error.message);
  } finally {
    loading.value = false;
  }
};

// 列印 QR Code
const printQRCode = () => {
  if (!selectedLocation.value) return;

  // 創建列印內容
  const printContent = `
    <html>
      <head>
        <title>位置 QR Code - ${selectedLocation.value.code}</title>
        <style>
          body {
            font-family: 'Microsoft JhengHei', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            text-align: center;
            background: white;
          }
          .qr-container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
          }
          .qr-title {
            font-size:2rem;
            font-weight: bold;
            margin: 0 auto 10px auto;
            color: #333;
            text-align: center;
          }
          .qr-title.shelf-size {
            font-size: 1rem; /* 縮小一半 */
          }
          .qr-subtitle {
            font-size: 2.5rem;
            color: #666;
            margin: 0 auto 10px auto;
            width: 70%;
            text-align: center;
          }
          .qr-subtitle.shelf-size {
            font-size: 1.25rem; /* 縮小一半 */
          }
          .qr-image {
            margin: 20px 0;
          }
          .qr-info {
            margin-top: 20px;
            font-size: 14px;
            color: #888;
          }
          @media print {
            body { margin: 0; padding: 10px; }
            .qr-container { border: none; box-shadow: none; }
          }
        </style>
      </head>
      <body>
        <div class="qr-container">
          <div class="qr-title ${selectedLocation.value.storageType === 'Shelf' ? 'shelf-size' : ''}">${selectedLocation.value.code}</div>
          <div class="qr-subtitle ${selectedLocation.value.storageType === 'Shelf' ? 'shelf-size' : ''}">${selectedLocation.value.name}</div>
          <div class="qr-image">
            <img src="${qrCodeUrl.value}" alt="QR Code" style="width: 100%; height: 100%;" />
          </div>
        </div>
      </body>
    </html>
  `;

  // 創建新視窗並列印
  const printWindow = window.open('', '_blank', 'width=600,height=800');
  if (printWindow) {
    printWindow.document.write(printContent);
    printWindow.document.close();

    // 等待圖片載入完成後執行列印
    printWindow.onload = () => {
      setTimeout(() => {
        printWindow.print();
        printWindow.close();
      }, 500);
    };
  }
};

// 關閉 Modal
const closeModal = () => {
  showEditModal.value = false;
  showQRModal.value = false;
  showDetailsModal.value = false;
  selectedLocation.value = null;
  locationItems.value = [];
  floorDistribution.value = [];
};

// 分頁切換
const changePage = (page) => {
  currentPage.value = page;
};

// 格式化使用率
const formatUtilization = (utilization) => {
  return utilization + '%';
};

// 格式化日期
const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// 自動生成位置代碼和名稱
const generateLocationCode = () => {
  if (selectedLocation.value &&
      selectedLocation.value.building &&
      selectedLocation.value.storageType &&
      selectedLocation.value.storageCode) {

    const building = selectedLocation.value.building;
    const storageType = selectedLocation.value.storageType;
    const positionCode = selectedLocation.value.storageCode;

    // 生成位置代碼
    selectedLocation.value.code = `${building}-${storageType}-${positionCode}`;

    // 生成位置名稱
    const storageTypeText = storageType === 'Shelf' ? '層架' : '棧板';
    selectedLocation.value.name = `${building}-${storageTypeText}-${positionCode}`;
  }
};

// 監聽位置相關欄位的變化
watch(() => selectedLocation.value?.building, generateLocationCode);
watch(() => selectedLocation.value?.storageType, generateLocationCode);
watch(() => selectedLocation.value?.storageCode, generateLocationCode);

// 批次列印相關函數
// 切換選取狀態
const toggleLocationSelection = (locationId) => {
  if (selectedLocations.value.has(locationId)) {
    selectedLocations.value.delete(locationId);
  } else {
    selectedLocations.value.add(locationId);
  }
};

// 全選/取消全選
const toggleSelectAll = () => {
  if (selectedDateRange.value || customDate.value) {
    // 如果有日期篩選，根據篩選條件進行全選/取消全選
    const filteredIds = new Set(filteredLocations.value.map(location => location.id));
    const selectedFilteredIds = Array.from(selectedLocations.value).filter(id => filteredIds.has(id));
    
    if (selectedFilteredIds.length === filteredLocations.value.length) {
      // 如果已全選，則取消選取所有符合篩選條件的位置
      filteredLocations.value.forEach(location => {
        selectedLocations.value.delete(location.id);
      });
    } else {
      // 如果未全選，則選取所有符合篩選條件的位置
      filteredLocations.value.forEach(location => {
        selectedLocations.value.add(location.id);
      });
    }
  } else {
    // 如果沒有日期篩選，則按原來的邏輯處理
    if (selectedLocations.value.size === paginatedLocations.value.length) {
      selectedLocations.value.clear();
    } else {
      paginatedLocations.value.forEach(location => {
        selectedLocations.value.add(location.id);
      });
    }
  }
};

// 根據日期篩選全選
const selectAllByDateFilter = () => {
  if (selectedDateRange.value || customDate.value) {
    // 根據日期篩選選取所有符合條件的位置
    filteredLocations.value.forEach(location => {
      selectedLocations.value.add(location.id);
    });
  } else {
    // 如果沒有日期篩選，則選取當前頁面的所有位置
    paginatedLocations.value.forEach(location => {
      selectedLocations.value.add(location.id);
    });
  }
};

// 檢查是否全選
const isAllSelected = computed(() => {
  if (selectedDateRange.value || customDate.value) {
    // 如果有日期篩選，檢查是否選取了所有符合篩選條件的位置
    const filteredIds = new Set(filteredLocations.value.map(location => location.id));
    const selectedFilteredIds = Array.from(selectedLocations.value).filter(id => filteredIds.has(id));
    return filteredLocations.value.length > 0 && selectedFilteredIds.length === filteredLocations.value.length;
  } else {
    // 如果沒有日期篩選，檢查是否選取了當前頁面的所有位置
    return paginatedLocations.value.length > 0 && 
           selectedLocations.value.size === paginatedLocations.value.length;
  }
});

// 檢查是否有部分選取
const isPartiallySelected = computed(() => {
  if (selectedDateRange.value || customDate.value) {
    // 如果有日期篩選，檢查是否有部分選取符合篩選條件的位置
    const filteredIds = new Set(filteredLocations.value.map(location => location.id));
    const selectedFilteredIds = Array.from(selectedLocations.value).filter(id => filteredIds.has(id));
    return selectedFilteredIds.length > 0 && selectedFilteredIds.length < filteredLocations.value.length;
  } else {
    // 如果沒有日期篩選，檢查是否有部分選取當前頁面的位置
    return selectedLocations.value.size > 0 && 
           selectedLocations.value.size < paginatedLocations.value.length;
  }
});

// 批次列印 QR Code
const batchPrintQRCodes = async () => {
  if (selectedLocations.value.size === 0) {
    alert(t('locations.batchPrint.noSelection'));
    return;
  }
  showPrintTemplateModal.value = true;
};

const handleTemplateSelected = (template) => {
  console.log('Template selected:', template);
  console.log('selectedLocations size:', selectedLocations.value.size);
  console.log('selectedLocation:', selectedLocation.value);
  
  showPrintTemplateModal.value = false;
  
  // 檢查是單個列印還是批次列印
  if (selectedLocations.value.size > 0) {
    console.log('執行批次列印');
    // 批次列印
    executeBatchPrint(template);
  } else if (selectedLocation.value) {
    console.log('執行單個列印');
    // 單個列印
    executeSinglePrint(template);
  } else {
    console.log('沒有選擇的位置');
    alert('沒有選擇的位置資料');
  }
};

// 執行單個列印
const executeSinglePrint = async (template = 'template1') => {
  console.log('executeSinglePrint called with template:', template);
  console.log('selectedLocation:', selectedLocation.value);
  
  if (!selectedLocation.value) {
    console.log('selectedLocation is null, returning');
    return;
  }
  
  try {
    // 確保 qrCodeUrl 已設置
    if (!qrCodeUrl.value) {
      const originalCode = selectedLocation.value.qrData || selectedLocation.value.code || selectedLocation.value.location_code || '';
      const transformedCode = transformLocationCode(originalCode);
      qrCodeUrl.value = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(transformedCode)}`;
      console.log('Generated qrCodeUrl:', qrCodeUrl.value);
    }
    // 創建單個位置的列印內容
    const printContent = `
      <html>
        <head>
          <title>位置 QR Code - ${selectedLocation.value.code}</title>
          <style>
            body {
              font-family: 'Microsoft JhengHei', Arial, sans-serif;
              margin: 0;
              padding: 0;
              background: white;
            }
            .qr-page {
              width: 100%;
              height: 100vh;
              display: flex;
              flex-direction: column;
              justify-content: center;
              align-items: center;
              page-break-after: always;
              page-break-inside: avoid;
              padding: 20px;
              box-sizing: border-box;
            }
            .qr-container {
              width: 100%;
              height: 100%;
              display: flex;
              flex-direction: column;
              justify-content: flex-start;
              align-items: center;
              text-align: center;
              background: white;
              padding-top: 0px;
            }
            .qr-container.template2 {
              flex-direction: row;
              text-align: left;
            }
            .qr-title {
              font-size: 3rem;
              font-weight: bold;
              margin: 0 auto 10px auto;
              color: #333;

              text-align: center;
            }
            .qr-title.shelf-size {
              font-size: 1.5rem; /* 縮小一半 */
            }
            .qr-subtitle {
              font-size: 2rem;
              color: #666;
              margin: 0 auto 10px auto;
              width: 70%;
              text-align: center;
            }
            .qr-subtitle.shelf-size {
              font-size: 1rem; /* 縮小一半 */
            }
            .qr-image {
              flex: 0 0 auto;
              display: flex;
              align-items: flex-start;
              justify-content: center;
              width: 70%;
              height: 100%;

              margin-bottom: -30px;
            }
            .qr-image.template2 {
              flex: 0 0 40%;
              justify-content: flex-start;
            }
            .qr-image img {
              width: 70%;
              height: 100%;
              max-height: 70%;
              object-fit: contain;
            }
            .qr-details.template2 {
              flex: 1;
              padding-left: 20px;
            }
            .qr-info {
              margin-top: 15px;
              font-size: 1rem;
              color: #888;
            }
            @media print {
              body { margin: 0; padding: 0; }
              .qr-page { 
                page-break-after: always; 
                height: 100vh;
                padding: 0;
                border: none;
                outline: none;
              }
              .qr-container { 
                width: 100%;
                height: 100%;
                box-shadow: none;
                border: none;
                outline: none;
              }
              .qr-image img {
                width: 70%;
                height: 100%;
                max-height: 70%;
              }
              .qr-details {
                border: none;
                outline: none;
              }
            }
            @page {
              size: ${template === 'template1' ? '40mm 30mm' : '30mm 40mm'};
            }
          </style>
        </head>
        <body>
          <div class="qr-page">
            <div class="qr-container ${template === 'template2' ? 'template2' : ''}">
              <div class="qr-image ${template === 'template2' ? 'template2' : ''}">
                <img src="${qrCodeUrl.value}" alt="QR Code" />
              </div>
              <div class="qr-details ${template === 'template2' ? 'template2' : ''}" style="margin-top: -40px;">
                <div class="qr-title ${selectedLocation.value.storageType === 'Shelf' ? 'shelf-size' : ''}">${transformLocationCode(selectedLocation.value.code)}</div>
              
              </div>
            </div>
          </div>
        </body>
      </html>
    `;

    // 創建新視窗並列印
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    if (printWindow) {
      printWindow.document.write(printContent);
      printWindow.document.close();

      // 只顯示預覽，不自動列印
      printWindow.onload = () => {
        // 預覽視窗載入完成，用戶可以在預覽中選擇列印
        console.log('預覽視窗已載入完成');
      };
    }
  } catch (error) {
    console.error('單個列印失敗:', error);
    alert('單個列印失敗: ' + error.message);
  }
};

// 執行實際的批次列印
const executeBatchPrint = async (template = 'template1') => {
  batchPrintLoading.value = true;
  
  try {
    // 獲取選取的位置資料
    const selectedLocationData = paginatedLocations.value.filter(location => 
      selectedLocations.value.has(location.id)
    );

    // 創建批次列印內容
    const printContent = `
      <html>
        <head>
          <title>批次位置 QR Code 列印</title>
          <style>
            body {
              font-family: 'Microsoft JhengHei', Arial, sans-serif;
              margin: 0;
              padding: 0;
              background: white;
            }
            .qr-page {
              width: 100%;
              height: 100vh;
              display: flex;
              flex-direction: column;
              justify-content: center;
              align-items: center;
              page-break-after: always;
              page-break-inside: avoid;
              padding: 20px;
              box-sizing: border-box;
            }
            .qr-container {
              width: 100%;
              height: 100%;
              display: flex;
              flex-direction: column;
              justify-content: flex-start;
              align-items: center;
              text-align: center;
              background: white;
              padding-top: 0px;
            }
            .qr-container.template2 {
              flex-direction: row;
              text-align: left;
            }
            .qr-title {
              font-size: 2rem;
              font-weight: bold;
              margin: 0 auto 10px auto;
              color: #333;
              text-align: center;
            }
            .qr-title.shelf-size {
              font-size: 1.5rem; /* 縮小一半 */
            }
            .qr-subtitle {
              font-size: 2rem;
              color: #666;
              margin: 0 auto 10px auto;
              width: 70%;
              text-align: center;
            }
            .qr-subtitle.shelf-size {
              font-size: 1rem; /* 縮小一半 */
            }
            .qr-image {
              flex: 0 0 auto;
              display: flex;
              align-items: flex-start;
              justify-content: center;
              width: 100%;
              height: 100%;
              margin-top:1rem;
              margin-bottom: -30px;
            }
            .qr-image.template2 {
              flex: 0 0 40%;
              justify-content: flex-start;
            }
            .qr-image img {
              width: 70%;
              height: 100%;
              max-height: 60%;
              object-fit: contain;
            }
            .qr-details.template2 {
              flex: 1;
              padding-left: 20px;
            }
            .qr-info {
              margin-top: 15px;
              font-size: 1rem;
              color: #888;
            }
            @media print {
              body { margin: 0; padding: 0; }
              .qr-page { 
                page-break-after: always; 
                height: 100vh;
                padding: 0;
                border: none;
                outline: none;
              }
              .qr-container { 
                width: 100%;
                height: 100%;
                box-shadow: none;
                border: none;
                outline: none;
              }
              .qr-image img {
                width: 70%;
                height: 100%;
                max-height: 60%;
                object-fit: contain;
                border: none;
                outline: none;
              }
              .qr-details {
                border: none;
                outline: none;
              }
            }
            @page {
              size: ${template === 'template2' ? '40mm 30mm' : '95mm 95mm'};
              margin: ${template === 'template2' ? '6mm' : '5mm'};
            }
          </style>
        </head>
        <body>
          ${selectedLocationData.map(location => `
            <div class="qr-page">
              <div class="qr-container ${template === 'template2' ? 'template2' : ''}">
                <div class="qr-image ${template === 'template2' ? 'template2' : ''}">
                  <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(transformLocationCode(location.qrData || location.code || location.location_code || ''))}" alt="QR Code" />
                </div>
                <div class="qr-details ${template === 'template2' ? 'template2' : ''}" style="margin-top: -40px;">
                  <div class="qr-title ${location.storageType === 'Shelf' ? 'shelf-size' : ''}">${transformLocationCode(location.code)}</div>
              
                </div>
              </div>
            </div>
          `).join('')}
        </body>
      </html>
    `;

    // 創建新視窗並列印
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    if (printWindow) {
      printWindow.document.write(printContent);
      printWindow.document.close();

      // 只顯示預覽，不自動列印
      printWindow.onload = () => {
        // 預覽視窗載入完成，用戶可以在預覽中選擇列印
        console.log('批次列印預覽視窗已載入完成');
        
        // 清空選取狀態（在預覽視窗打開後立即清空）
        selectedLocations.value.clear();
        showBatchPrintMode.value = false;
      };
    }
  } catch (error) {
    console.error('批次列印失敗:', error);
    alert('批次列印失敗: ' + error.message);
  } finally {
    batchPrintLoading.value = false;
  }
};

// 開啟批次列印模式
const enableBatchPrintMode = () => {
  showBatchPrintMode.value = !showBatchPrintMode.value;
  if (!showBatchPrintMode.value) {
    selectedLocations.value.clear();
  }
};

// 關閉批次列印模式
const closeBatchPrintModal = () => {
  showBatchPrintMode.value = false;
  selectedLocations.value.clear();
};

onMounted(() => {
  loadLocations();
});
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- 頁面標題 -->
    <div class="bg-white shadow-sm border-b">
      <div class="px-6 py-4">
        <h1 class="text-2xl font-bold text-teal-600">{{ t('locations.title') }}</h1>
      </div>
    </div>

    <!-- 搜尋和篩選區域 -->
    <div class="bg-white shadow-sm border-b">
      <div class="px-6 py-4">
        <div class="flex flex-wrap items-center gap-4">
          <!-- 搜尋框 -->
          <div class="flex-1 min-w-64">
            <input
              v-model="searchText"
              type="text"
              :placeholder="t('locations.search.placeholder')"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
            />
          </div>

          <!-- 建築篩選 -->
          <select
            v-model="selectedBuilding"
            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
          >
            <option value="">{{ t('locations.search.allBuildings') }}</option>
            <option value="TP">{{ t('locations.buildings.taipei') }}</option>
            <option value="CH">{{ t('locations.buildings.changhua') }}</option>
          </select>

          <!-- 類別篩選 -->
          <select
            v-model="selectedCategory"
            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
          >
            <option value="">{{ t('locations.search.allCategories') }}</option>
            <option value="Area">{{ t('locations.categories.area') }}</option>
            <option value="Shelf">{{ t('locations.categories.shelf') }}</option>
          </select>

          <!-- 日期範圍篩選 -->
          <select
            v-model="selectedDateRange"
            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
          >
            <option value="">{{ t('locations.search.allDates') }}</option>
            <option value="today">{{ t('locations.search.today') }}</option>
            <option value="yesterday">{{ t('locations.search.yesterday') }}</option>
            <option value="thisWeek">{{ t('locations.search.thisWeek') }}</option>
            <option value="lastWeek">{{ t('locations.search.lastWeek') }}</option>
            <option value="thisMonth">{{ t('locations.search.thisMonth') }}</option>
            <option value="lastMonth">{{ t('locations.search.lastMonth') }}</option>
          </select>

          <!-- 自訂日期搜尋 -->
          <input
            v-model="customDate"
            type="date"
            :placeholder="t('locations.search.customDate')"
            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
          />

          <!-- 搜尋按鈕 -->
          <button
            @click="handleSearch"
            class="px-4 py-2 bg-teal-600 text-white rounded-md hover:bg-teal-700 transition-colors"
          >
            {{ t('locations.search.button') }}
          </button>

          <!-- 操作按鈕 -->
          <button
            @click="enableBatchPrintMode"
            :class="showBatchPrintMode ? 'px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors' : 'px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors'">
            {{ showBatchPrintMode ? t('locations.batchPrint.startPrint') : t('locations.batchPrint.enableMode') }}
          </button>
          <button
            @click="downloadTemplate"
            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
            {{ t('locations.search.downloadTemplate') }}
          </button>
          <label class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors cursor-pointer">
            {{ t('locations.search.import') }}
            <input
              type="file"
              accept=".csv"
              class="hidden"
              @change="handleFileImport"
            />
          </label>
        </div>
      </div>
    </div>

    <!-- 載入中狀態 -->
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-teal-600"></div>
      <span class="ml-2 text-gray-600">{{ t('common.loading') }}</span>
    </div>

    <!-- 錯誤訊息 -->
    <div v-else-if="error" class="px-6 py-4">
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <strong>錯誤:</strong> {{ error }}
        <button @click="loadLocations" class="ml-4 text-red-600 hover:text-red-800 underline">
          重試
        </button>
      </div>
    </div>

    <!-- 資料表格 -->
    <div v-else class="px-6 py-4">
      <!-- 批次列印控制區域 -->
      <div v-if="showBatchPrintMode" class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <span class="text-blue-800 font-medium">
              {{ t('locations.batchPrint.selectedLocations', { count: selectedLocations.size }) }}
              <span v-if="selectedDateRange" class="text-sm text-blue-600">
                ({{ t('locations.search.' + selectedDateRange) }})
              </span>
              <span v-if="customDate" class="text-sm text-blue-600">
                ({{ t('locations.search.customDate') }}: {{ formatDate(customDate) }})
              </span>
            </span>
            <button
              v-if="selectedDateRange || customDate"
              @click="selectAllByDateFilter"
              class="px-3 py-1 bg-green-600 text-white rounded text-xs hover:bg-green-700 transition-colors"
            >
              {{ t('locations.batchPrint.selectAllFiltered') }}
            </button>
            <button
              @click="batchPrintQRCodes"
              :disabled="selectedLocations.size === 0"
              class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ t('locations.batchPrint.startBatchPrint') }}
            </button>
          </div>
          <button
            @click="closeBatchPrintModal"
            class="text-blue-600 hover:text-blue-800 underline"
          >
            {{ t('locations.batchPrint.cancelBatchPrint') }}
          </button>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-teal-600 text-white">
              <tr>
                <th v-if="showBatchPrintMode" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                  <input
                    type="checkbox"
                    :checked="isAllSelected"
                    :indeterminate="isPartiallySelected"
                    @change="toggleSelectAll"
                    class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500 focus:ring-2"
                  />
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                  {{ t('locations.table.locationCode') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                  {{ t('locations.table.locationName') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                  {{ t('locations.table.building') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                  {{ t('locations.table.storageType') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                  {{ t('locations.table.storageCode') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                  {{ t('locations.table.createdAt') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                  {{ t('locations.table.notes') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                  {{ t('locations.table.actions') }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="location in paginatedLocations" :key="location.id" class="hover:bg-gray-50">
                <td v-if="showBatchPrintMode" class="px-6 py-4 whitespace-nowrap">
                  <input
                    type="checkbox"
                    :checked="selectedLocations.has(location.id)"
                    @change="toggleLocationSelection(location.id)"
                    class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500 focus:ring-2"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <a href="#" @click.prevent="showDetails(location)" class="text-teal-600 hover:text-teal-900 font-medium">
                    {{ location.code }}
                  </a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ location.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ location.building }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ location.storageType }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ location.storageCode }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(location.createdAt) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ location.notes }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      @click="showDetails(location)"
                      class="bg-teal-600 text-white px-3 py-1 rounded text-xs hover:bg-teal-700 transition-colors"
                    >
                      {{ t('locations.actions.details') }}
                    </button>
                    <button
                      @click="showQRCode(location)"
                      class="bg-gray-600 text-white px-3 py-1 rounded text-xs hover:bg-gray-700 transition-colors"
                    >
                      {{ t('locations.actions.qrCode') }}
                    </button>
                    <button
                      @click="editLocation(location)"
                      class="bg-teal-600 text-white px-3 py-1 rounded text-xs hover:bg-teal-700 transition-colors"
                    >
                      {{ t('locations.actions.edit') }}
                    </button>
                    <button
                      @click="deleteLocation(location)"
                      class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700 transition-colors"
                    >
                      {{ t('locations.actions.delete') }}
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 分頁 -->
        <div class="bg-white px-6 py-3 border-t border-gray-200 flex items-center justify-between">
          <div class="flex items-center">
            <span class="text-sm text-gray-700">{{ t('locations.pagination.itemsPerPage') }}</span>
            <select
              v-model="itemsPerPage"
              class="ml-2 px-2 py-1 border border-gray-300 rounded text-sm"
            >
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
            </select>
            <span class="ml-4 text-sm text-gray-700">
              {{ t('locations.pagination.page') }} {{ currentPage }} {{ t('locations.pagination.totalPages') }} {{ totalPages }} {{ t('locations.pagination.pages') }}
            </span>
          </div>
          <div class="flex items-center space-x-2">
            <button
              @click="changePage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="px-3 py-1 bg-gray-300 text-gray-700 rounded text-sm hover:bg-gray-400 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ t('locations.pagination.prev') }}
            </button>
            <span class="text-sm text-gray-700">{{ currentPage }}</span>
            <button
              @click="changePage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="px-3 py-1 bg-gray-300 text-gray-700 rounded text-sm hover:bg-gray-400 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ t('locations.pagination.next') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 編輯 Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900">{{ t('locations.modal.editTitle') }}</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div v-if="selectedLocation" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                位置代碼 (自動生成)
              </label>
              <input
                v-model="selectedLocation.code"
                type="text"
                readonly
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600 cursor-not-allowed"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                位置名稱 (自動生成)
              </label>
              <input
                v-model="selectedLocation.name"
                type="text"
                readonly
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600 cursor-not-allowed"
              />
            </div>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                建築代碼 (Building_code)
              </label>
              <select
                v-model="selectedLocation.building"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
              >
                <option value="">請選擇建築</option>
                <option value="TP">TP - 台北</option>
                <option value="CH">CH - 彰化</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                存放類別代碼 (Storage_type_code)
              </label>
              <select
                v-model="selectedLocation.storageType"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
              >
                <option value="">請選擇存放類別</option>
                <option value="Area">Area - 區域</option>
                <option value="Shelf">Shelf - 層架</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                位置代碼 (Position_code)
              </label>
              <input
                v-model="selectedLocation.storageCode"
                type="text"
                placeholder="如: C201, S101"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              備註
            </label>
            <textarea
              v-model="selectedLocation.notes"
              rows="3"
              placeholder="輸入備註資訊..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
            ></textarea>
          </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button
            @click="closeModal"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
          >
            {{ t('locations.modal.cancel') }}
          </button>
          <button
            @click="saveLocation"
            class="px-4 py-2 bg-teal-600 text-white rounded-md hover:bg-teal-700 transition-colors"
          >
            {{ t('locations.modal.save') }}
          </button>
        </div>
      </div>
    </div>

    <!-- QR Code Modal -->
    <div v-if="showQRModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-sm mx-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900">{{ t('locations.qrModal.title') }}</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div class="text-center">
          <div class="mb-4">
            <img :src="qrCodeUrl" alt="QR Code" class="mx-auto" />
          </div>
          <p class="text-sm text-gray-600 mb-2">{{ selectedLocation?.code }}</p>
          <p class="text-sm text-gray-500">{{ selectedLocation?.name }}</p>
        </div>

        <div class="flex justify-center space-x-3 mt-6">
          <button
            @click="closeModal"
            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors"
          >
            關閉
          </button>
          <button
            @click="handlePrintFromModal"
            class="px-4 py-2 bg-teal-600 text-white rounded-md hover:bg-teal-700 transition-colors"
          >
            列印
          </button>
        </div>
      </div>
    </div>

    <!-- 詳細資訊 Modal -->
    <div v-if="showDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b">
          <div class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">
              位置歸位資訊 - {{ selectedLocation?.code }}
            </h3>
          </div>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Modal Content -->
        <div class="p-6">
          <!-- 位置基本資訊 -->
          <div class="grid grid-cols-2 gap-6 mb-6">
            <div class="space-y-2">
              <div class="flex">
                <span class="text-gray-600 w-32">位置代碼：</span>
                <span class="text-gray-900">{{ selectedLocation?.code }}</span>
              </div>
              <div class="flex">
                <span class="text-gray-600 w-32">位置名稱：</span>
                <span class="text-gray-900">{{ selectedLocation?.name }}</span>
              </div>
              <div class="flex">
                <span class="text-gray-600 w-32">建築代碼：</span>
                <span class="text-gray-900">{{ selectedLocation?.building }}</span>
              </div>
            </div>
            <div class="space-y-2">
              <div class="flex">
                <span class="text-gray-600 w-32">存放類別代碼：</span>
                <span class="text-gray-900">{{ selectedLocation?.storageType }}</span>
              </div>
              <div class="flex">
                <span class="text-gray-600 w-32">位置代碼：</span>
                <span class="text-gray-900">{{ selectedLocation?.storageCode }}</span>
              </div>
              <div class="flex">
                <span class="text-gray-600 w-32">備註：</span>
                <span class="text-gray-900">{{ selectedLocation?.notes || '-' }}</span>
              </div>
            </div>
          </div>

          <!-- 層架分佈 (只有Shelf類型才顯示) -->
          <div v-if="selectedLocation?.storageType === 'Shelf'" class="mb-6">
            <div class="flex items-center space-x-2 mb-4">
              <div class="w-6 h-6 bg-orange-500 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                </svg>
              </div>
              <h4 class="text-lg font-semibold text-gray-900">層架分佈</h4>
            </div>

            <div v-if="floorDistribution.length > 0" class="grid grid-cols-3 gap-4">
              <div v-for="floor in floorDistribution" :key="floor.floor" class="bg-gray-50 p-4 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-teal-500 rounded-full"></div>
                    <span class="font-medium text-gray-900">第 {{ floor.floor }} 層</span>
                  </div>
                </div>
                <div class="text-sm text-gray-600 mb-1">{{ floor.itemCount }} 個商品</div>
                <div class="text-sm text-gray-600 mb-1">{{ floor.uniqueItems }} 種不同商品</div>
                <div class="text-xs text-gray-500">
                  商品: {{ floor.items.join(', ') }}
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              此層架位置目前沒有商品分布資料
            </div>
          </div>

          <!-- 該位置商品清單 -->
          <div>
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center space-x-2">
                <div class="w-6 h-6 bg-orange-500 rounded-full flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                  </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900">該位置商品清單 ({{ locationItems.length }} 項)</h4>
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">商品貨號</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">批號</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">箱號</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">層架</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">QR Code內容</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">到期日</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">生成時間</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">生成者</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-if="locationItems.length === 0">
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                      目前此位置沒有商品
                    </td>
                  </tr>
                  <tr v-for="item in locationItems" :key="item.qr_id || item.id" class="hover:bg-gray-50">
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                      {{ item.item_code }}
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                      {{ item.item_batch }}
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                      {{ item.box_number }}
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                      <span v-if="item.floor_level" class="px-2 py-1 text-xs font-medium bg-teal-100 text-teal-800 rounded">
                        第{{ item.floor_level }}層
                      </span>
                      <span v-else class="text-gray-400">-</span>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                      <div class="max-w-xs truncate" :title="item.qr_content">
                        {{ item.qr_content || '-' }}
                      </div>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                      {{ item.expiry_date ? new Date(item.expiry_date).toLocaleDateString() : '-' }}
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                      {{ item.generated_at ? new Date(item.generated_at).toLocaleDateString() : '-' }}
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                      {{ item.generated_by || '-' }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t">
          <button
            @click="closeModal"
            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors"
          >
            關閉
          </button>
        </div>
      </div>
    </div>

    <!-- Print Template Modal -->
    <PrintTemplateModal
      v-if="showPrintTemplateModal"
      @close="showPrintTemplateModal = false"
      @select="handleTemplateSelected"
    />
  </div>
</template>

<style scoped>
/* 自訂樣式 */
</style>
