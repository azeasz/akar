import React, { useState, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  StatusBar,
  RefreshControl,
  Image
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { getMyChecklists } from '../../services/checklistService';
import { useAuth } from '../../context/AuthContext';

const ChecklistItem = ({ item, onPress }) => {
  const formatDate = (dateString) => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = date.toLocaleString('id-ID', { month: 'short' }).toUpperCase();
    return { day, month };
  };

  const getTypeIcon = (type) => {
    const typeMap = {
      'perburuan': 'target',
      'lomba': 'trophy',
      'perdagangan': 'cart',
      'pemeliharaan': 'home',
      'penangkaran': 'paw',
      'pemeliharaan & penangkaran': 'home',
    };
    return typeMap[type.toLowerCase()] || 'list';
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

  const { day, month } = formatDate(item.tanggal);
  const iconName = getTypeIcon(item.type);
  const iconColor = getTypeColor(item.type);

  return (
    <TouchableOpacity style={styles.itemContainer} onPress={() => onPress(item)}>
      <View style={[styles.iconContainer, { backgroundColor: iconColor }]}>
        <Ionicons name={iconName} size={24} color="#fff" />
      </View>
      <View style={styles.itemContent}>
        <Text style={styles.itemTitle}>{item.type}</Text>
        <Text style={styles.itemLocation}>{item.nama_lokasi || 'Unknown place'}</Text>
        <Text style={styles.itemDetails}>
          {item.faunas?.length || 1} sp | {item.faunas?.reduce((sum, fauna) => sum + fauna.jumlah, 0) || item.faunas?.length || 1} ind
        </Text>
      </View>
      <View style={styles.dateContainer}>
        <Text style={styles.dateDay}>{day}</Text>
        <Text style={styles.dateMonth}>{month}</Text>
      </View>
    </TouchableOpacity>
  );
};

const MyChecklistScreen = ({ navigation }) => {
  const { user } = useAuth();
  const [checklists, setChecklists] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState(null);

  const fetchChecklists = useCallback(async () => {
    try {
      setError(null);
      const data = await getMyChecklists();
      setChecklists(data);
    } catch (err) {
      console.error('Error fetching checklists:', err);
      setError('Gagal memuat data observasi. Silakan coba lagi.');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      setLoading(true);
      fetchChecklists();
      return () => {};
    }, [fetchChecklists])
  );

  const handleRefresh = () => {
    setRefreshing(true);
    fetchChecklists();
  };

  const handleChecklistPress = (checklist) => {
    navigation.navigate('ChecklistDetail', { checklistId: checklist.id });
  };

  const renderEmptyList = () => (
    <View style={styles.emptyContainer}>
      <Image 
        source={require('../../assets/empty-list.png')} 
        style={styles.emptyImage} 
        resizeMode="contain" 
      />
      <Text style={styles.emptyText}>Belum ada observasi</Text>
      <Text style={styles.emptySubText}>
        Tambahkan observasi baru dengan menekan tombol + di bawah
      </Text>
    </View>
  );

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#000000" />
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Observasi saya</Text>
        <Text style={styles.headerUsername}>@{user?.username || 'user'}</Text>
      </View>

      <View style={styles.searchContainer}>
        <TouchableOpacity 
          style={styles.searchBox}
          onPress={() => navigation.navigate('SearchChecklist')}
        >
          <Ionicons name="search" size={24} color="#777" />
          <Text style={styles.searchText}>Cari</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.filterButton}>
          <Ionicons name="grid" size={24} color="#fff" />
        </TouchableOpacity>
        <TouchableOpacity style={styles.mapButton}>
          <Ionicons name="map" size={24} color="#fff" />
        </TouchableOpacity>
      </View>

      {loading && !refreshing ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#bf6420" />
        </View>
      ) : error ? (
        <View style={styles.errorContainer}>
          <Text style={styles.errorText}>{error}</Text>
          <TouchableOpacity style={styles.retryButton} onPress={fetchChecklists}>
            <Text style={styles.retryButtonText}>Coba Lagi</Text>
          </TouchableOpacity>
        </View>
      ) : (
        <FlatList
          data={checklists}
          renderItem={({ item }) => (
            <ChecklistItem item={item} onPress={handleChecklistPress} />
          )}
          keyExtractor={(item) => item.id.toString()}
          contentContainerStyle={checklists.length === 0 ? { flex: 1 } : styles.listContent}
          ListEmptyComponent={renderEmptyList}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={handleRefresh}
              colors={['#bf6420']}
              tintColor="#bf6420"
            />
          }
        />
      )}

      <TouchableOpacity
        style={styles.fab}
        onPress={() => navigation.navigate('CreateChecklist')}
      >
        <Ionicons name="add" size={30} color="#fff" />
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000000',
  },
  header: {
    padding: 15,
    paddingTop: 10,
    backgroundColor: '#000000',
    borderBottomWidth: 0,
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  headerUsername: {
    fontSize: 14,
    color: '#888888',
  },
  searchContainer: {
    flexDirection: 'row',
    padding: 10,
    paddingBottom: 15,
    backgroundColor: '#000000',
  },
  searchBox: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#222222',
    borderRadius: 8,
    padding: 10,
    marginRight: 10,
  },
  searchText: {
    color: '#777777',
    marginLeft: 10,
    fontSize: 16,
  },
  filterButton: {
    backgroundColor: '#222222',
    borderRadius: 8,
    padding: 10,
    marginRight: 10,
    justifyContent: 'center',
    alignItems: 'center',
  },
  mapButton: {
    backgroundColor: '#222222',
    borderRadius: 8,
    padding: 10,
    justifyContent: 'center',
    alignItems: 'center',
  },
  listContent: {
    paddingBottom: 80,
  },
  itemContainer: {
    flexDirection: 'row',
    backgroundColor: '#111111',
    borderBottomWidth: 1,
    borderBottomColor: '#222222',
    padding: 15,
  },
  iconContainer: {
    width: 50,
    height: 50,
    borderRadius: 8,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 15,
  },
  itemContent: {
    flex: 1,
    justifyContent: 'center',
  },
  itemTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  itemLocation: {
    fontSize: 14,
    color: '#888888',
    marginVertical: 2,
  },
  itemDetails: {
    fontSize: 12,
    color: '#666666',
  },
  dateContainer: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  dateDay: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#bf6420',
  },
  dateMonth: {
    fontSize: 14,
    color: '#bf6420',
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
    color: '#ff6b6b',
    textAlign: 'center',
    marginBottom: 15,
  },
  retryButton: {
    backgroundColor: '#bf6420',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 5,
  },
  retryButtonText: {
    color: '#ffffff',
    fontWeight: 'bold',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  emptyImage: {
    width: 150,
    height: 150,
    marginBottom: 20,
    tintColor: '#444444',
  },
  emptyText: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#ffffff',
    marginBottom: 10,
  },
  emptySubText: {
    fontSize: 14,
    color: '#888888',
    textAlign: 'center',
  },
  fab: {
    position: 'absolute',
    width: 60,
    height: 60,
    backgroundColor: '#bf6420',
    borderRadius: 30,
    justifyContent: 'center',
    alignItems: 'center',
    bottom: 20,
    right: 20,
    elevation: 5,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 3,
  },
});

export default MyChecklistScreen; 