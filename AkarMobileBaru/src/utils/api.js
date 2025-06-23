import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Base URL untuk API
const API_URL = 'https://api.akar.co.id/api';

// Membuat instance axios dengan konfigurasi default
const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Interceptor untuk menambahkan token ke setiap request
api.interceptors.request.use(
  async (config) => {
    const token = await AsyncStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Interceptor untuk menangani response dan error
api.interceptors.response.use(
  (response) => {
    return response;
  },
  async (error) => {
    // Jika error 401 (Unauthorized), hapus token dan redirect ke login
    if (error.response && error.response.status === 401) {
      await AsyncStorage.removeItem('token');
      await AsyncStorage.removeItem('user');
      // Redirect ke login akan ditangani oleh AuthContext
    }
    return Promise.reject(error);
  }
);

export default api; 