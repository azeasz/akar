import React, { useState } from 'react';
import { View, Text, StyleSheet, TextInput, TouchableOpacity, Alert, ScrollView } from 'react-native';
import { router } from 'expo-router';

export default function Register() {
  const [formData, setFormData] = useState({
    username: '',
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
    reason: '',
    organization: '',
    phone: ''
  });

  const handleChange = (key, value) => {
    setFormData({
      ...formData,
      [key]: value
    });
  };

  const handleRegister = () => {
    const { username, name, email, password, confirmPassword } = formData;
    
    if (!username || !name || !email || !password || !confirmPassword) {
      Alert.alert('Error', 'Mohon isi semua field yang wajib');
      return;
    }
    
    if (password !== confirmPassword) {
      Alert.alert('Error', 'Password tidak cocok');
      return;
    }
    
    // Simulasi registrasi berhasil
    Alert.alert('Sukses', 'Pendaftaran berhasil', [
      { text: 'OK', onPress: () => router.replace('/login') }
    ]);
  };

  return (
    <ScrollView style={styles.container}>
      <Text style={styles.title}>Daftar</Text>
      
      <View style={styles.inputContainer}>
        <Text style={styles.label}>Username <Text style={styles.required}>*</Text></Text>
        <TextInput
          style={styles.input}
          value={formData.username}
          onChangeText={(text) => handleChange('username', text)}
          placeholder="Masukkan username"
          placeholderTextColor="#666"
        />
      </View>
      
      <View style={styles.inputContainer}>
        <Text style={styles.label}>Nama Lengkap <Text style={styles.required}>*</Text></Text>
        <TextInput
          style={styles.input}
          value={formData.name}
          onChangeText={(text) => handleChange('name', text)}
          placeholder="Masukkan nama lengkap"
          placeholderTextColor="#666"
        />
      </View>
      
      <View style={styles.inputContainer}>
        <Text style={styles.label}>Email <Text style={styles.required}>*</Text></Text>
        <TextInput
          style={styles.input}
          value={formData.email}
          onChangeText={(text) => handleChange('email', text)}
          placeholder="Masukkan email"
          placeholderTextColor="#666"
          keyboardType="email-address"
        />
      </View>
      
      <View style={styles.inputContainer}>
        <Text style={styles.label}>Password <Text style={styles.required}>*</Text></Text>
        <TextInput
          style={styles.input}
          value={formData.password}
          onChangeText={(text) => handleChange('password', text)}
          placeholder="Masukkan password"
          placeholderTextColor="#666"
          secureTextEntry
        />
      </View>
      
      <View style={styles.inputContainer}>
        <Text style={styles.label}>Konfirmasi Password <Text style={styles.required}>*</Text></Text>
        <TextInput
          style={styles.input}
          value={formData.confirmPassword}
          onChangeText={(text) => handleChange('confirmPassword', text)}
          placeholder="Konfirmasi password"
          placeholderTextColor="#666"
          secureTextEntry
        />
      </View>
      
      <View style={styles.inputContainer}>
        <Text style={styles.label}>Alasan Bergabung</Text>
        <TextInput
          style={[styles.input, styles.textArea]}
          value={formData.reason}
          onChangeText={(text) => handleChange('reason', text)}
          placeholder="Alasan bergabung dengan AKAR"
          placeholderTextColor="#666"
          multiline
          numberOfLines={4}
        />
      </View>
      
      <View style={styles.inputContainer}>
        <Text style={styles.label}>Organisasi</Text>
        <TextInput
          style={styles.input}
          value={formData.organization}
          onChangeText={(text) => handleChange('organization', text)}
          placeholder="Nama organisasi (opsional)"
          placeholderTextColor="#666"
        />
      </View>
      
      <View style={styles.inputContainer}>
        <Text style={styles.label}>Nomor Telepon</Text>
        <TextInput
          style={styles.input}
          value={formData.phone}
          onChangeText={(text) => handleChange('phone', text)}
          placeholder="Nomor telepon (opsional)"
          placeholderTextColor="#666"
          keyboardType="phone-pad"
        />
      </View>
      
      <TouchableOpacity style={styles.button} onPress={handleRegister}>
        <Text style={styles.buttonText}>Daftar</Text>
      </TouchableOpacity>
      
      <TouchableOpacity style={styles.linkContainer} onPress={() => router.back()}>
        <Text style={styles.linkText}>
          Sudah punya akun? <Text style={styles.link}>Login</Text>
        </Text>
      </TouchableOpacity>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000000',
    padding: 20,
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#bf6420',
    marginBottom: 30,
    marginTop: 20,
    textAlign: 'center',
  },
  inputContainer: {
    marginBottom: 15,
  },
  label: {
    color: '#ffffff',
    marginBottom: 5,
    fontSize: 16,
  },
  required: {
    color: '#bf6420',
  },
  input: {
    backgroundColor: '#121212',
    color: '#ffffff',
    padding: 12,
    borderRadius: 8,
    fontSize: 16,
    borderWidth: 1,
    borderColor: '#333',
  },
  textArea: {
    height: 100,
    textAlignVertical: 'top',
  },
  button: {
    backgroundColor: '#bf6420',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
    marginTop: 10,
    marginBottom: 20,
  },
  buttonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  linkContainer: {
    marginBottom: 30,
    alignItems: 'center',
  },
  linkText: {
    color: '#ffffff',
    textAlign: 'center',
  },
  link: {
    color: '#bf6420',
    fontWeight: 'bold',
  },
});