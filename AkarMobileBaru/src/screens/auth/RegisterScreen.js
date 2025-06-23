import React, { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, Alert } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useAuth } from '../../context/AuthContext';
import Input from '../../components/Input';
import Button from '../../components/Button';

const RegisterScreen = ({ navigation }) => {
  const [formData, setFormData] = useState({
    username: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    reason: '',
    organisasi: '',
    phone_number: '',
  });
  const [errors, setErrors] = useState({});
  const { register, loading } = useAuth();

  const handleChange = (key, value) => {
    setFormData({
      ...formData,
      [key]: value,
    });
  };

  const validate = () => {
    let isValid = true;
    let errors = {};

    if (!formData.username.trim()) {
      errors.username = 'Username harus diisi';
      isValid = false;
    }

    if (!formData.name.trim()) {
      errors.name = 'Nama harus diisi';
      isValid = false;
    }

    if (!formData.email.trim()) {
      errors.email = 'Email harus diisi';
      isValid = false;
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      errors.email = 'Format email tidak valid';
      isValid = false;
    }

    if (!formData.password) {
      errors.password = 'Password harus diisi';
      isValid = false;
    } else if (formData.password.length < 8) {
      errors.password = 'Password minimal 8 karakter';
      isValid = false;
    }

    if (formData.password !== formData.password_confirmation) {
      errors.password_confirmation = 'Konfirmasi password tidak cocok';
      isValid = false;
    }

    if (!formData.reason.trim()) {
      errors.reason = 'Alasan pendaftaran harus diisi';
      isValid = false;
    }

    setErrors(errors);
    return isValid;
  };

  const handleRegister = async () => {
    if (validate()) {
      try {
        await register(formData);
        Alert.alert(
          'Registrasi Berhasil',
          'Akun Anda telah dibuat. Silakan tunggu persetujuan dari admin.',
          [{ text: 'OK', onPress: () => navigation.navigate('Login') }]
        );
      } catch (error) {
        Alert.alert(
          'Registrasi Gagal',
          error.message || 'Terjadi kesalahan saat registrasi. Silakan coba lagi.'
        );
      }
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView contentContainerStyle={styles.scrollView}>
        <View style={styles.container}>
          <Text style={styles.title}>Daftar Akun Baru</Text>
          <Text style={styles.subtitle}>
            Silakan isi formulir di bawah ini untuk mendaftar
          </Text>

          <View style={styles.form}>
            <Input
              label="Username"
              value={formData.username}
              onChangeText={(text) => handleChange('username', text)}
              placeholder="Masukkan username"
              error={errors.username}
              autoCapitalize="none"
            />

            <Input
              label="Nama Lengkap"
              value={formData.name}
              onChangeText={(text) => handleChange('name', text)}
              placeholder="Masukkan nama lengkap"
              error={errors.name}
              autoCapitalize="words"
            />

            <Input
              label="Email"
              value={formData.email}
              onChangeText={(text) => handleChange('email', text)}
              placeholder="Masukkan email"
              keyboardType="email-address"
              error={errors.email}
              autoCapitalize="none"
            />

            <Input
              label="Password"
              value={formData.password}
              onChangeText={(text) => handleChange('password', text)}
              placeholder="Masukkan password"
              secureTextEntry
              error={errors.password}
            />

            <Input
              label="Konfirmasi Password"
              value={formData.password_confirmation}
              onChangeText={(text) => handleChange('password_confirmation', text)}
              placeholder="Masukkan konfirmasi password"
              secureTextEntry
              error={errors.password_confirmation}
            />

            <Input
              label="Organisasi"
              value={formData.organisasi}
              onChangeText={(text) => handleChange('organisasi', text)}
              placeholder="Masukkan nama organisasi (opsional)"
              error={errors.organisasi}
            />

            <Input
              label="Nomor Telepon"
              value={formData.phone_number}
              onChangeText={(text) => handleChange('phone_number', text)}
              placeholder="Masukkan nomor telepon (opsional)"
              keyboardType="phone-pad"
              error={errors.phone_number}
            />

            <Input
              label="Alasan Pendaftaran"
              value={formData.reason}
              onChangeText={(text) => handleChange('reason', text)}
              placeholder="Masukkan alasan pendaftaran"
              multiline
              numberOfLines={4}
              error={errors.reason}
            />

            <Button
              title="Daftar"
              onPress={handleRegister}
              loading={loading}
              style={styles.registerButton}
            />

            <View style={styles.loginContainer}>
              <Text style={styles.loginText}>Sudah punya akun? </Text>
              <Button
                title="Masuk"
                variant="outline"
                onPress={() => navigation.navigate('Login')}
                style={styles.loginButton}
                textStyle={styles.loginButtonText}
              />
            </View>
          </View>
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
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#333',
    marginTop: 20,
    marginBottom: 10,
  },
  subtitle: {
    fontSize: 16,
    color: '#666',
    marginBottom: 20,
  },
  form: {
    width: '100%',
  },
  registerButton: {
    marginTop: 20,
  },
  loginContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 20,
    marginBottom: 30,
  },
  loginText: {
    fontSize: 16,
    color: '#666',
  },
  loginButton: {
    padding: 0,
    backgroundColor: 'transparent',
    borderWidth: 0,
  },
  loginButtonText: {
    color: '#bf6420',
    fontWeight: 'bold',
    fontSize: 16,
  },
});

export default RegisterScreen; 