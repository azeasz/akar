import React from 'react';
import { View, Text, StyleSheet, ScrollView, Image, Alert } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../context/AuthContext';
import Button from '../../components/Button';

const ProfileScreen = () => {
  const { user, logout, loading } = useAuth();

  const handleLogout = async () => {
    Alert.alert(
      'Konfirmasi Logout',
      'Apakah Anda yakin ingin keluar dari aplikasi?',
      [
        { text: 'Batal', style: 'cancel' },
        {
          text: 'Logout',
          onPress: async () => {
            try {
              await logout();
              // Logout berhasil, navigasi akan ditangani oleh AppNavigator
            } catch (error) {
              Alert.alert('Logout Gagal', error.message || 'Terjadi kesalahan saat logout');
            }
          },
        },
      ]
    );
  };

  const renderProfileItem = (icon, label, value) => (
    <View style={styles.profileItem}>
      <View style={styles.profileItemIcon}>
        <Ionicons name={icon} size={24} color="#bf6420" />
      </View>
      <View style={styles.profileItemContent}>
        <Text style={styles.profileItemLabel}>{label}</Text>
        <Text style={styles.profileItemValue}>{value || '-'}</Text>
      </View>
    </View>
  );

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView contentContainerStyle={styles.scrollView}>
        <View style={styles.container}>
          <View style={styles.header}>
            <View style={styles.profileImageContainer}>
              {user?.profile_picture ? (
                <Image
                  source={{ uri: user.profile_picture }}
                  style={styles.profileImage}
                />
              ) : (
                <View style={styles.profileImagePlaceholder}>
                  <Text style={styles.profileImagePlaceholderText}>
                    {user?.name?.charAt(0) || 'U'}
                  </Text>
                </View>
              )}
            </View>
            <Text style={styles.name}>{user?.name}</Text>
            <Text style={styles.username}>@{user?.username}</Text>
          </View>

          <View style={styles.profileSection}>
            <Text style={styles.sectionTitle}>Informasi Pribadi</Text>
            {renderProfileItem('mail-outline', 'Email', user?.email)}
            {renderProfileItem('call-outline', 'Telepon', user?.phone_number)}
            {renderProfileItem('business-outline', 'Organisasi', user?.organisasi)}
            {user?.reason && renderProfileItem('document-text-outline', 'Alasan Bergabung', user?.reason)}
          </View>

          <Button
            title="Logout"
            onPress={handleLogout}
            loading={loading}
            variant="danger"
            style={styles.logoutButton}
          />
        </View>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#fff',
  },
  scrollView: {
    flexGrow: 1,
  },
  container: {
    flex: 1,
    padding: 20,
  },
  header: {
    alignItems: 'center',
    marginBottom: 30,
  },
  profileImageContainer: {
    marginBottom: 15,
  },
  profileImage: {
    width: 100,
    height: 100,
    borderRadius: 50,
  },
  profileImagePlaceholder: {
    width: 100,
    height: 100,
    borderRadius: 50,
    backgroundColor: '#bf6420',
    justifyContent: 'center',
    alignItems: 'center',
  },
  profileImagePlaceholderText: {
    fontSize: 40,
    fontWeight: 'bold',
    color: '#fff',
  },
  name: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#333',
  },
  username: {
    fontSize: 16,
    color: '#666',
    marginTop: 5,
  },
  profileSection: {
    backgroundColor: '#f9f9f9',
    borderRadius: 10,
    padding: 15,
    marginBottom: 20,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 15,
  },
  profileItem: {
    flexDirection: 'row',
    marginBottom: 15,
  },
  profileItemIcon: {
    width: 40,
    alignItems: 'center',
  },
  profileItemContent: {
    flex: 1,
  },
  profileItemLabel: {
    fontSize: 14,
    color: '#666',
  },
  profileItemValue: {
    fontSize: 16,
    color: '#333',
    fontWeight: '500',
  },
  logoutButton: {
    marginTop: 20,
  },
});

export default ProfileScreen; 