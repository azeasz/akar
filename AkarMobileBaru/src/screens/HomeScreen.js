import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Image,
  StatusBar,
  ActivityIndicator,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../context/AuthContext';
import { getMyChecklists } from '../services/checklistService';

const HomeScreen = ({ navigation }) => {
  const { user } = useAuth();
  const [recentChecklists, setRecentChecklists] = useState([]);
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState({
    total: 0,
    completed: 0,
    species: 0,
  });

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      setLoading(true);
      const data = await getMyChecklists();
      
      // Hanya ambil 5 checklist terbaru
      const recent = data.slice(0, 5);
      setRecentChecklists(recent);
      
      // Hitung statistik
      const completed = data.filter(item => item.is_completed).length;
      const totalSpecies = data.reduce((sum, item) => {
        return sum + (item.faunas?.length || 0);
      }, 0);
      
      setStats({
        total: data.length,
        completed,
        species: totalSpecies,
      });
    } catch (err) {
      console.error('Error fetching data:', err);
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateString) => {
    const options = { day: 'numeric', month: 'short', year: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
  };

  const getTypeColor = (type) => {
    const typeMap = {
      'perburuan': '#e74c3c',
      'lomba': '#f39c12',
      'perdagangan': '#3498db',
      'pemeliharaan': '#2ecc71',
      'penangkaran': '#9b59b6',
      'pemeliharaan & penangkaran': '#1abc9c',
    };
    return typeMap[type.toLowerCase()] || '#95a5a6';
  };

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#000000" />
      
      {/* Header */}
      <View style={styles.header}>
        <View>
          <Text style={styles.welcomeText}>Selamat Datang,</Text>
          <Text style={styles.username}>{user?.name || 'Pengguna'}</Text>
        </View>
        <TouchableOpacity 
          style={styles.profileButton}
          onPress={() => navigation.navigate('Profile')}
        >
          {user?.profile_picture ? (
            <Image 
              source={{ uri: user.profile_picture }} 
              style={styles.profileImage} 
            />
          ) : (
            <View style={styles.profilePlaceholder}>
              <Text style={styles.profileInitial}>
                {user?.name?.charAt(0).toUpperCase() || 'U'}
              </Text>
            </View>
          )}
        </TouchableOpacity>
      </View>

      {loading ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#bf6420" />
        </View>
      ) : (
        <ScrollView style={styles.scrollView} showsVerticalScrollIndicator={false}>
          {/* Stats Cards */}
          <View style={styles.statsContainer}>
            <View style={styles.statsCard}>
              <Ionicons name="list" size={24} color="#bf6420" />
              <Text style={styles.statsNumber}>{stats.total}</Text>
              <Text style={styles.statsLabel}>Total Observasi</Text>
            </View>
            
            <View style={styles.statsCard}>
              <Ionicons name="checkmark-circle" size={24} color="#bf6420" />
              <Text style={styles.statsNumber}>{stats.completed}</Text>
              <Text style={styles.statsLabel}>Selesai</Text>
            </View>
            
            <View style={styles.statsCard}>
              <Ionicons name="paw" size={24} color="#bf6420" />
              <Text style={styles.statsNumber}>{stats.species}</Text>
              <Text style={styles.statsLabel}>Spesies</Text>
            </View>
          </View>
          
          {/* Quick Actions */}
          <View style={styles.sectionContainer}>
            <Text style={styles.sectionTitle}>Aksi Cepat</Text>
            <View style={styles.actionsContainer}>
              <TouchableOpacity 
                style={styles.actionButton}
                onPress={() => navigation.navigate('Checklists', { 
                  screen: 'CreateChecklist' 
                })}
              >
                <View style={styles.actionIconContainer}>
                  <Ionicons name="add" size={24} color="#fff" />
                </View>
                <Text style={styles.actionText}>Tambah Observasi</Text>
              </TouchableOpacity>
              
              <TouchableOpacity 
                style={styles.actionButton}
                onPress={() => navigation.navigate('Checklists', { 
                  screen: 'MyChecklists' 
                })}
              >
                <View style={styles.actionIconContainer}>
                  <Ionicons name="list" size={24} color="#fff" />
                </View>
                <Text style={styles.actionText}>Lihat Semua</Text>
              </TouchableOpacity>
              
              <TouchableOpacity 
                style={styles.actionButton}
                onPress={() => navigation.navigate('Checklists', { 
                  screen: 'SearchChecklist' 
                })}
              >
                <View style={styles.actionIconContainer}>
                  <Ionicons name="search" size={24} color="#fff" />
                </View>
                <Text style={styles.actionText}>Cari</Text>
              </TouchableOpacity>
            </View>
          </View>
          
          {/* Recent Checklists */}
          <View style={styles.sectionContainer}>
            <Text style={styles.sectionTitle}>Observasi Terbaru</Text>
            
            {recentChecklists.length === 0 ? (
              <View style={styles.emptyContainer}>
                <Ionicons name="document-text-outline" size={48} color="#444" />
                <Text style={styles.emptyText}>Belum ada observasi</Text>
                <TouchableOpacity 
                  style={styles.createButton}
                  onPress={() => navigation.navigate('Checklists', { 
                    screen: 'CreateChecklist' 
                  })}
                >
                  <Text style={styles.createButtonText}>Buat Observasi</Text>
                </TouchableOpacity>
              </View>
            ) : (
              recentChecklists.map((item) => (
                <TouchableOpacity 
                  key={item.id}
                  style={styles.checklistItem}
                  onPress={() => navigation.navigate('Checklists', {
                    screen: 'ChecklistDetail',
                    params: { checklistId: item.id }
                  })}
                >
                  <View style={[
                    styles.checklistTypeIndicator, 
                    { backgroundColor: getTypeColor(item.type) }
                  ]} />
                  <View style={styles.checklistContent}>
                    <Text style={styles.checklistType}>{item.type}</Text>
                    <Text style={styles.checklistLocation}>{item.nama_lokasi}</Text>
                    <Text style={styles.checklistDate}>{formatDate(item.tanggal)}</Text>
                  </View>
                  <Ionicons name="chevron-forward" size={20} color="#666" />
                </TouchableOpacity>
              ))
            )}
            
            {recentChecklists.length > 0 && (
              <TouchableOpacity 
                style={styles.viewAllButton}
                onPress={() => navigation.navigate('Checklists', { 
                  screen: 'MyChecklists' 
                })}
              >
                <Text style={styles.viewAllText}>Lihat Semua</Text>
                <Ionicons name="arrow-forward" size={16} color="#bf6420" />
              </TouchableOpacity>
            )}
          </View>
        </ScrollView>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000000',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: 50,
    paddingBottom: 20,
  },
  welcomeText: {
    fontSize: 16,
    color: '#888888',
  },
  username: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  profileButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    overflow: 'hidden',
  },
  profileImage: {
    width: '100%',
    height: '100%',
  },
  profilePlaceholder: {
    width: '100%',
    height: '100%',
    backgroundColor: '#bf6420',
    justifyContent: 'center',
    alignItems: 'center',
  },
  profileInitial: {
    color: '#ffffff',
    fontSize: 18,
    fontWeight: 'bold',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  scrollView: {
    flex: 1,
  },
  statsContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    marginTop: 10,
  },
  statsCard: {
    backgroundColor: '#121212',
    borderRadius: 10,
    padding: 15,
    width: '30%',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 3,
    elevation: 2,
  },
  statsNumber: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#ffffff',
    marginTop: 5,
  },
  statsLabel: {
    fontSize: 12,
    color: '#888888',
    marginTop: 2,
  },
  sectionContainer: {
    marginTop: 25,
    paddingHorizontal: 20,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#ffffff',
    marginBottom: 15,
  },
  actionsContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  actionButton: {
    backgroundColor: '#121212',
    borderRadius: 10,
    padding: 15,
    width: '30%',
    alignItems: 'center',
  },
  actionIconContainer: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: '#bf6420',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
  },
  actionText: {
    fontSize: 12,
    color: '#ffffff',
    textAlign: 'center',
  },
  emptyContainer: {
    backgroundColor: '#121212',
    borderRadius: 10,
    padding: 30,
    alignItems: 'center',
    marginBottom: 20,
  },
  emptyText: {
    fontSize: 16,
    color: '#888888',
    marginTop: 10,
    marginBottom: 15,
  },
  createButton: {
    backgroundColor: '#bf6420',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 5,
  },
  createButtonText: {
    color: '#ffffff',
    fontWeight: 'bold',
  },
  checklistItem: {
    flexDirection: 'row',
    backgroundColor: '#121212',
    borderRadius: 10,
    padding: 15,
    marginBottom: 10,
    alignItems: 'center',
  },
  checklistTypeIndicator: {
    width: 5,
    height: '80%',
    borderRadius: 3,
    marginRight: 15,
  },
  checklistContent: {
    flex: 1,
  },
  checklistType: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  checklistLocation: {
    fontSize: 14,
    color: '#888888',
    marginTop: 2,
  },
  checklistDate: {
    fontSize: 12,
    color: '#666666',
    marginTop: 2,
  },
  viewAllButton: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 10,
    marginTop: 5,
    marginBottom: 30,
  },
  viewAllText: {
    fontSize: 14,
    color: '#bf6420',
    fontWeight: 'bold',
    marginRight: 5,
  },
});

export default HomeScreen; 