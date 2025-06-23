import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  Switch,
  ScrollView,
  StatusBar,
  Alert,
  Linking,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../context/AuthContext';

const SettingsScreen = ({ navigation }) => {
  const { user } = useAuth();
  const [notificationsEnabled, setNotificationsEnabled] = useState(true);
  const [locationEnabled, setLocationEnabled] = useState(true);
  const [darkModeEnabled, setDarkModeEnabled] = useState(true);

  const toggleNotifications = () => {
    setNotificationsEnabled(previousState => !previousState);
  };

  const toggleLocation = () => {
    setLocationEnabled(previousState => !previousState);
  };

  const toggleDarkMode = () => {
    setDarkModeEnabled(previousState => !previousState);
    // Implementasi nyata akan mengubah tema aplikasi
  };

  const handleAbout = () => {
    Alert.alert(
      'Tentang AKAR',
      'AKAR adalah aplikasi untuk pendataan dan pemantauan fauna Indonesia. Versi 1.0.0',
      [{ text: 'OK' }]
    );
  };

  const handleHelp = () => {
    // Implementasi nyata akan membuka halaman bantuan
    Alert.alert(
      'Bantuan',
      'Untuk bantuan lebih lanjut, silakan hubungi support@akar.id',
      [{ text: 'OK' }]
    );
  };

  const handlePrivacyPolicy = () => {
    // Implementasi nyata akan membuka halaman kebijakan privasi
    Linking.openURL('https://akar.id/privacy-policy');
  };

  const handleTerms = () => {
    // Implementasi nyata akan membuka halaman syarat dan ketentuan
    Linking.openURL('https://akar.id/terms');
  };

  const renderSettingItem = (icon, title, rightElement) => (
    <View style={styles.settingItem}>
      <View style={styles.settingLeft}>
        <Ionicons name={icon} size={24} color="#bf6420" />
        <Text style={styles.settingTitle}>{title}</Text>
      </View>
      {rightElement}
    </View>
  );

  const renderSettingSwitch = (icon, title, value, onToggle) => (
    renderSettingItem(icon, title, 
      <Switch
        trackColor={{ false: '#444', true: '#bf6420' }}
        thumbColor={value ? '#fff' : '#f4f3f4'}
        ios_backgroundColor="#444"
        onValueChange={onToggle}
        value={value}
      />
    )
  );

  const renderSettingButton = (icon, title, onPress) => (
    <TouchableOpacity style={styles.settingItem} onPress={onPress}>
      <View style={styles.settingLeft}>
        <Ionicons name={icon} size={24} color="#bf6420" />
        <Text style={styles.settingTitle}>{title}</Text>
      </View>
      <Ionicons name="chevron-forward" size={20} color="#666" />
    </TouchableOpacity>
  );

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#000000" />
      
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Pengaturan</Text>
      </View>
      
      <ScrollView style={styles.scrollView}>
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Akun</Text>
          <View style={styles.sectionContent}>
            <TouchableOpacity 
              style={styles.profileItem}
              onPress={() => navigation.navigate('Profile')}
            >
              <View style={styles.profileLeft}>
                <View style={styles.profileImageContainer}>
                  <Text style={styles.profileInitial}>
                    {user?.name?.charAt(0).toUpperCase() || 'U'}
                  </Text>
                </View>
                <View>
                  <Text style={styles.profileName}>{user?.name || 'Pengguna'}</Text>
                  <Text style={styles.profileEmail}>{user?.email || 'user@example.com'}</Text>
                </View>
              </View>
              <Ionicons name="chevron-forward" size={20} color="#666" />
            </TouchableOpacity>
          </View>
        </View>
        
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Umum</Text>
          <View style={styles.sectionContent}>
            {renderSettingSwitch('notifications', 'Notifikasi', notificationsEnabled, toggleNotifications)}
            {renderSettingSwitch('location', 'Lokasi', locationEnabled, toggleLocation)}
            {renderSettingSwitch('moon', 'Mode Gelap', darkModeEnabled, toggleDarkMode)}
            {renderSettingButton('globe', 'Bahasa', () => {})}
          </View>
        </View>
        
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Informasi</Text>
          <View style={styles.sectionContent}>
            {renderSettingButton('help-circle', 'Bantuan', handleHelp)}
            {renderSettingButton('shield-checkmark', 'Kebijakan Privasi', handlePrivacyPolicy)}
            {renderSettingButton('document-text', 'Syarat dan Ketentuan', handleTerms)}
            {renderSettingButton('information-circle', 'Tentang', handleAbout)}
          </View>
        </View>
        
        <View style={styles.section}>
          <View style={styles.sectionContent}>
            <TouchableOpacity 
              style={[styles.settingItem, styles.logoutButton]}
              onPress={() => navigation.navigate('Profile')}
            >
              <Ionicons name="log-out" size={24} color="#e74c3c" />
              <Text style={styles.logoutText}>Keluar</Text>
            </TouchableOpacity>
          </View>
        </View>
        
        <View style={styles.footer}>
          <Text style={styles.footerText}>AKAR v1.0.0</Text>
          <Text style={styles.footerText}>© 2024 AKAR Indonesia</Text>
        </View>
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000000',
  },
  header: {
    paddingHorizontal: 20,
    paddingTop: 50,
    paddingBottom: 15,
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  scrollView: {
    flex: 1,
  },
  section: {
    marginBottom: 25,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#bf6420',
    paddingHorizontal: 20,
    marginBottom: 10,
  },
  sectionContent: {
    backgroundColor: '#121212',
    borderRadius: 10,
    marginHorizontal: 15,
    overflow: 'hidden',
  },
  profileItem: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 15,
  },
  profileLeft: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  profileImageContainer: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: '#bf6420',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 15,
  },
  profileInitial: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  profileName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  profileEmail: {
    fontSize: 14,
    color: '#888888',
  },
  settingItem: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#222222',
  },
  settingLeft: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  settingTitle: {
    fontSize: 16,
    color: '#ffffff',
    marginLeft: 15,
  },
  logoutButton: {
    justifyContent: 'center',
    borderBottomWidth: 0,
  },
  logoutText: {
    fontSize: 16,
    color: '#e74c3c',
    marginLeft: 15,
  },
  footer: {
    padding: 20,
    alignItems: 'center',
  },
  footerText: {
    fontSize: 12,
    color: '#666666',
    marginBottom: 5,
  },
});

export default SettingsScreen; 