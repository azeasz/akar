import React, { createContext, useContext, useReducer, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import * as authService from '../services/authService';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const initialState = {
    user: null,
    isAuthenticated: false,
    loading: true,
    error: null
  };

  const reducer = (state, action) => {
    switch (action.type) {
      case 'LOGIN_SUCCESS':
      case 'REGISTER_SUCCESS':
      case 'USER_LOADED':
        return {
          ...state,
          user: action.payload,
          isAuthenticated: true,
          loading: false,
          error: null
        };
      case 'AUTH_ERROR':
      case 'LOGIN_FAIL':
      case 'REGISTER_FAIL':
        return {
          ...state,
          error: action.payload,
          loading: false
        };
      case 'LOGOUT':
        return {
          ...state,
          user: null,
          isAuthenticated: false,
          loading: false,
          error: null
        };
      case 'CLEAR_ERROR':
        return {
          ...state,
          error: null
        };
      default:
        return state;
    }
  };

  const [state, dispatch] = useReducer(reducer, initialState);

  // Load user on first run or refresh
  useEffect(() => {
    loadUser();
  }, []);

  // Load user from storage or API
  const loadUser = async () => {
    try {
      const user = await authService.getCurrentUser();
      
      if (user) {
        dispatch({
          type: 'USER_LOADED',
          payload: user
        });
      } else {
        dispatch({ type: 'AUTH_ERROR' });
      }
    } catch (error) {
      dispatch({
        type: 'AUTH_ERROR',
        payload: error.message
      });
    }
  };

  // Login user
  const login = async (username, password) => {
    try {
      const user = await authService.login(username, password);
      
      dispatch({
        type: 'LOGIN_SUCCESS',
        payload: user
      });
      
      return { success: true };
    } catch (error) {
      dispatch({
        type: 'LOGIN_FAIL',
        payload: error.message
      });
      
      return { success: false, error: error.message };
    }
  };

  // Register user
  const register = async (userData) => {
    try {
      const user = await authService.register(userData);
      
      dispatch({
        type: 'REGISTER_SUCCESS',
        payload: user
      });
      
      return { success: true };
    } catch (error) {
      dispatch({
        type: 'REGISTER_FAIL',
        payload: error.message
      });
      
      return { success: false, error: error.message };
    }
  };

  // Logout user
  const logout = async () => {
    try {
      await authService.logout();
      dispatch({ type: 'LOGOUT' });
      return { success: true };
    } catch (error) {
      return { success: false, error: error.message };
    }
  };

  // Clear errors
  const clearError = () => {
    dispatch({ type: 'CLEAR_ERROR' });
  };

  return (
    <AuthContext.Provider
      value={{
        state,
        login,
        register,
        logout,
        clearError,
        loadUser
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}; 