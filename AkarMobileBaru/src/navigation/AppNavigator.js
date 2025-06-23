import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createStackNavigator } from '@react-navigation/stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../context/AuthContext';

// Import screens
import LoginScreen from '../screens/auth/LoginScreen';
import RegisterScreen from '../screens/auth/RegisterScreen';
import ProfileScreen from '../screens/auth/ProfileScreen';
import HomeScreen from '../screens/HomeScreen';
import MyChecklistScreen from '../screens/checklist/MyChecklistScreen';
import ChecklistDetailScreen from '../screens/checklist/ChecklistDetailScreen';
import SearchChecklistScreen from '../screens/SearchChecklistScreen';
import SettingsScreen from '../screens/SettingsScreen';

// Define themes
import { DarkTheme } from '@react-navigation/native';

// Create custom theme
const AkarTheme = {
  ...DarkTheme,
  colors: {
    ...DarkTheme.colors,
    primary: '#bf6420',
    background: '#000000',
    card: '#121212',
    text: '#ffffff',
    border: '#333333',
    notification: '#bf6420',
  },
};

const Stack = createStackNavigator();
const Tab = createBottomTabNavigator();

// Auth Navigator
const AuthNavigator = () => {
  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: { backgroundColor: '#000000' },
        headerTintColor: '#bf6420',
        cardStyle: { backgroundColor: '#000000' }
      }}
    >
      <Stack.Screen name="Login" component={LoginScreen} options={{ headerShown: false }} />
      <Stack.Screen name="Register" component={RegisterScreen} options={{ title: 'Daftar' }} />
    </Stack.Navigator>
  );
};

// Checklist Navigator
const ChecklistNavigator = () => {
  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: { backgroundColor: '#000000' },
        headerTintColor: '#bf6420',
        cardStyle: { backgroundColor: '#000000' }
      }}
    >
      <Stack.Screen 
        name="MyChecklists" 
        component={MyChecklistScreen} 
        options={{ title: 'Observasi Saya' }} 
      />
      <Stack.Screen 
        name="ChecklistDetail" 
        component={ChecklistDetailScreen} 
        options={{ title: 'Detail Observasi' }} 
      />
    </Stack.Navigator>
  );
};

// Main Navigator with Tabs
const MainNavigator = () => {
  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        tabBarIcon: ({ focused, color, size }) => {
          let iconName;

          if (route.name === 'Home') {
            iconName = focused ? 'home' : 'home-outline';
          } else if (route.name === 'Checklists') {
            iconName = focused ? 'list' : 'list-outline';
          } else if (route.name === 'Search') {
            iconName = focused ? 'search' : 'search-outline';
          } else if (route.name === 'Profile') {
            iconName = focused ? 'person' : 'person-outline';
          } else if (route.name === 'Settings') {
            iconName = focused ? 'settings' : 'settings-outline';
          }

          return <Ionicons name={iconName} size={size} color={color} />;
        },
        tabBarActiveTintColor: '#bf6420',
        tabBarInactiveTintColor: 'gray',
        tabBarStyle: {
          backgroundColor: '#000000',
          borderTopColor: '#333333',
          paddingBottom: 5,
          paddingTop: 5,
          height: 60
        },
        headerStyle: {
          backgroundColor: '#000000',
        },
        headerTintColor: '#bf6420',
        headerTitleStyle: {
          fontWeight: 'bold',
        },
        cardStyle: { backgroundColor: '#000000' }
      })}
    >
      <Tab.Screen 
        name="Home" 
        component={HomeScreen} 
        options={{
          title: 'Beranda',
          headerShown: true
        }}
      />
      <Tab.Screen 
        name="Checklists" 
        component={ChecklistNavigator} 
        options={{
          title: 'Observasi',
          headerShown: false
        }}
      />
      <Tab.Screen 
        name="Search" 
        component={SearchChecklistScreen} 
        options={{
          title: 'Cari',
          headerShown: true
        }}
      />
      <Tab.Screen 
        name="Profile" 
        component={ProfileScreen} 
        options={{
          title: 'Profil',
          headerShown: true
        }}
      />
      <Tab.Screen 
        name="Settings" 
        component={SettingsScreen} 
        options={{
          title: 'Pengaturan',
          headerShown: true
        }}
      />
    </Tab.Navigator>
  );
};

// Main App Navigator
const AppNavigator = () => {
  const { state } = useAuth();

  return (
    <NavigationContainer theme={AkarTheme}>
      {state.isAuthenticated ? <MainNavigator /> : <AuthNavigator />}
    </NavigationContainer>
  );
};

export default AppNavigator; 