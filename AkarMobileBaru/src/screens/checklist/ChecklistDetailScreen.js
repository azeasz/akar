import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  ActivityIndicator,
  Alert,
  TouchableOpacity,
  Image,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import * as checklistService from '../../services/checklistService';
import Button from '../../components/Button';

const ChecklistDetailScreen = ({ route, navigation }) => {
  const { checklistId } = route.params;
  const [checklist, setChecklist] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [completingChecklist, setCompletingChecklist] = useState(false);

  useEffect(() => {
    fetchChecklistDetail();
  }, [checklistId]);

  const fetchChecklistDetail = async () => {
    try {
      setLoading(true);
      setError(null);
      const response = await checklistService.getChecklistDetail(checklistId);
      setChecklist(response.data);
    } catch (err) {
      setError(err.message || 'Terjadi kesalahan saat mengambil detail checklist');
      Alert.alert(
        'Error',
        err.message || 'Terjadi kesalahan saat mengambil detail checklist'
      );
    } finally {
      setLoading(false);
    }
  };

  const handleCompleteChecklist = async () => {
    Alert.alert(
      'Konfirmasi',
      'Apakah Anda yakin ingin menyelesaikan checklist ini?',
      [
        { text: 'Batal', style: 'cancel' },
        {
          text: 'Ya, Selesaikan',
          onPress: async () => {
            try {
              setCompletingChecklist(true);
              await checklistService.completeChecklist(checklistId);
              Alert.alert('Sukses', 'Checklist berhasil diselesaikan');
              fetchChecklistDetail(); // Refresh data
            } catch (err) {
              Alert.alert(
                'Error',
                err.message || 'Terjadi kesalahan saat menyelesaikan checklist'
              );
            } finally {
              setCompletingChecklist(false);
            }
          },
        },
      ]
    );
  };

  const handleDeleteChecklist = () => {
    Alert.alert(
      'Konfirmasi Hapus',
      'Apakah Anda yakin ingin menghapus checklist ini? Tindakan ini tidak dapat dibatalkan.',
      [
        { text: 'Batal', style: 'cancel' },
        {
          text: 'Hapus',
          style: 'destructive',
          onPress: async () => {
            try {
              await checklistService.deleteChecklist(checklistId);
              Alert.alert('Sukses', 'Checklist berhasil dihapus');
              navigation.goBack();
            } catch (err) {
              Alert.alert(
                'Error',
                err.message || 'Terjadi kesalahan saat menghapus checklist'
              );
            }
          },
        },
      ]
    );
  };

  const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
  };

  // Mendapatkan warna berdasarkan tipe checklist
  const getTypeColor = (type) => {
    switch (type?.toLowerCase()) {
      case 'perburuan':
        return '#dc3545';
      case 'lomba':
        return '#ffc107';
      case 'perdagangan':
        return '#17a2b8';
      case 'pemeliharaan':
        return '#28a745';
      case 'penangkaran':
        return '#6610f2';
      case 'pemeliharaan & penangkaran':
        return '#20c997';
      default:
        return '#6c757d';
    }
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#bf6420" />
      </SafeAreaView>
    );
  }

  if (error) {
    return (
      <SafeAreaView style={styles.errorContainer}>
        <Ionicons name="alert-circle-outline" size={60} color="#dc3545" />
        <Text style={styles.errorText}>Terjadi kesalahan</Text>
        <Text style={styles.errorSubText}>{error}</Text>
        <Button
          title="Coba Lagi"
          onPress={fetchChecklistDetail}
          style={styles.retryButton}
        />
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView contentContainerStyle={styles.scrollView}>
        {/* Header Info */}
        <View style={styles.header}>
          <View
            style={[
              styles.typeTag,
              { backgroundColor: getTypeColor(checklist?.type) },
            ]}
          >
            <Text style={styles.typeText}>{checklist?.type}</Text>
          </View>

          <Text style={styles.title}>{checklist?.nama_lokasi}</Text>

          <View style={styles.infoRow}>
            <Ionicons name="calendar-outline" size={18} color="#666" />
            <Text style={styles.infoText}>
              {checklist?.tanggal ? formatDate(checklist.tanggal) : '-'}
            </Text>
          </View>

          <View style={styles.infoRow}>
            <Ionicons name="location-outline" size={18} color="#666" />
            <Text style={styles.infoText}>
              {checklist?.latitude && checklist?.longitude
                ? `${checklist.latitude.toFixed(4)}, ${checklist.longitude.toFixed(4)}`
                : 'Lokasi tidak tersedia'}
            </Text>
          </View>

          {checklist?.pemilik && (
            <View style={styles.infoRow}>
              <Ionicons name="person-outline" size={18} color="#666" />
              <Text style={styles.infoText}>{checklist.pemilik}</Text>
            </View>
          )}

          <View
            style={[
              styles.statusTag,
              {
                backgroundColor: checklist?.is_completed ? '#28a745' : '#ffc107',
              },
            ]}
          >
            <Text style={styles.statusText}>
              {checklist?.is_completed ? 'Selesai' : 'Draft'}
            </Text>
          </View>
        </View>

        {/* Catatan */}
        {checklist?.catatan && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Catatan</Text>
            <Text style={styles.catatanText}>{checklist.catatan}</Text>
          </View>
        )}

        {/* Fauna List */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Fauna</Text>
          {checklist?.faunas && checklist.faunas.length > 0 ? (
            checklist.faunas.map((fauna) => (
              <View key={fauna.id} style={styles.faunaItem}>
                <Text style={styles.faunaName}>{fauna.nama_spesies}</Text>
                <View style={styles.faunaDetails}>
                  <View style={styles.faunaDetail}>
                    <Ionicons name="stats-chart-outline" size={16} color="#666" />
                    <Text style={styles.faunaDetailText}>
                      Jumlah: {fauna.jumlah}
                    </Text>
                  </View>
                  
                  {fauna.gender && (
                    <View style={styles.faunaDetail}>
                      <Ionicons
                        name={
                          fauna.gender === 'Jantan'
                            ? 'male-outline'
                            : fauna.gender === 'Betina'
                            ? 'female-outline'
                            : 'help-circle-outline'
                        }
                        size={16}
                        color="#666"
                      />
                      <Text style={styles.faunaDetailText}>{fauna.gender}</Text>
                    </View>
                  )}
                  
                  {fauna.status_buruan && (
                    <View style={styles.faunaDetail}>
                      <Ionicons
                        name={
                          fauna.status_buruan === 'hidup'
                            ? 'heart-outline'
                            : 'heart-dislike-outline'
                        }
                        size={16}
                        color="#666"
                      />
                      <Text style={styles.faunaDetailText}>
                        {fauna.status_buruan === 'hidup' ? 'Hidup' : 'Mati'}
                      </Text>
                    </View>
                  )}
                </View>
                
                {fauna.catatan && (
                  <Text style={styles.faunaCatatan}>{fauna.catatan}</Text>
                )}
              </View>
            ))
          ) : (
            <Text style={styles.emptyText}>Belum ada data fauna</Text>
          )}
        </View>

        {/* Images */}
        {checklist?.images && checklist.images.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Gambar</Text>
            <ScrollView horizontal showsHorizontalScrollIndicator={false}>
              {checklist.images.map((image, index) => (
                <View key={index} style={styles.imageContainer}>
                  <Image
                    source={{ uri: image.image_path }}
                    style={styles.image}
                    resizeMode="cover"
                  />
                </View>
              ))}
            </ScrollView>
          </View>
        )}

        {/* Action Buttons */}
        <View style={styles.actionButtons}>
          {!checklist?.is_completed && (
            <Button
              title="Selesaikan Checklist"
              onPress={handleCompleteChecklist}
              loading={completingChecklist}
              style={styles.completeButton}
            />
          )}
          
          <Button
            title="Edit Checklist"
            variant="secondary"
            onPress={() =>
              navigation.navigate('CreateChecklist', {
                checklistId: checklist?.id,
                isEditing: true,
              })
            }
            style={styles.editButton}
          />
          
          <Button
            title="Hapus Checklist"
            variant="danger"
            onPress={handleDeleteChecklist}
            style={styles.deleteButton}
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
    padding: 15,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  errorText: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginTop: 10,
  },
  errorSubText: {
    fontSize: 14,
    color: '#666',
    marginTop: 5,
    textAlign: 'center',
  },
  retryButton: {
    marginTop: 20,
    width: 150,
  },
  header: {
    backgroundColor: '#f9f9f9',
    borderRadius: 10,
    padding: 15,
    marginBottom: 15,
  },
  typeTag: {
    alignSelf: 'flex-start',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 5,
    marginBottom: 10,
  },
  typeText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 12,
  },
  title: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 10,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  infoText: {
    fontSize: 16,
    color: '#333',
    marginLeft: 8,
  },
  statusTag: {
    alignSelf: 'flex-start',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 5,
    marginTop: 10,
  },
  statusText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 12,
  },
  section: {
    backgroundColor: '#f9f9f9',
    borderRadius: 10,
    padding: 15,
    marginBottom: 15,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 10,
  },
  catatanText: {
    fontSize: 16,
    color: '#333',
    lineHeight: 22,
  },
  faunaItem: {
    backgroundColor: '#fff',
    borderRadius: 8,
    padding: 12,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: '#eee',
  },
  faunaName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 5,
  },
  faunaDetails: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginBottom: 5,
  },
  faunaDetail: {
    flexDirection: 'row',
    alignItems: 'center',
    marginRight: 15,
    marginBottom: 5,
  },
  faunaDetailText: {
    fontSize: 14,
    color: '#666',
    marginLeft: 5,
  },
  faunaCatatan: {
    fontSize: 14,
    color: '#666',
    fontStyle: 'italic',
    marginTop: 5,
  },
  emptyText: {
    fontSize: 16,
    color: '#666',
    fontStyle: 'italic',
  },
  imageContainer: {
    marginRight: 10,
    borderRadius: 8,
    overflow: 'hidden',
  },
  image: {
    width: 150,
    height: 150,
  },
  actionButtons: {
    marginTop: 10,
    marginBottom: 30,
  },
  completeButton: {
    marginBottom: 10,
  },
  editButton: {
    marginBottom: 10,
  },
  deleteButton: {
    marginBottom: 10,
  },
});

export default ChecklistDetailScreen; 