import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TextInput,
  TouchableOpacity,
  FlatList,
  ActivityIndicator,
  StatusBar,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { getMyChecklists } from '../services/checklistService';
import ChecklistCard from '../components/ChecklistCard';

const SearchChecklistScreen = ({ navigation }) => {
  const [searchQuery, setSearchQuery] = useState('');
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);
  const [searched, setSearched] = useState(false);

  const handleSearch = async () => {
    if (!searchQuery.trim()) return;
    
    setLoading(true);
    setSearched(true);
    
    try {
      // Dalam kasus nyata, Anda akan memanggil API dengan parameter pencarian
      // Untuk contoh ini, kita ambil semua checklist dan filter secara lokal
      const data = await getMyChecklists();
      
      const filtered = data.filter(item => {
        const query = searchQuery.toLowerCase();
        return (
          (item.nama_lokasi && item.nama_lokasi.toLowerCase().includes(query)) ||
          (item.type && item.type.toLowerCase().includes(query)) ||
          (item.pemilik && item.pemilik.toLowerCase().includes(query)) ||
          (item.catatan && item.catatan.toLowerCase().includes(query))
        );
      });
      
      setResults(filtered);
    } catch (error) {
      console.error('Error searching checklists:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleChecklistPress = (checklist) => {
    navigation.navigate('ChecklistDetail', { checklistId: checklist.id });
  };

  const renderEmptyResult = () => {
    if (!searched) return null;
    
    return (
      <View style={styles.emptyContainer}>
        <Ionicons name="search-outline" size={64} color="#444444" />
        <Text style={styles.emptyText}>Tidak ada hasil</Text>
        <Text style={styles.emptySubText}>
          Coba kata kunci lain atau ubah filter pencarian
        </Text>
      </View>
    );
  };

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#000000" />
      
      <View style={styles.header}>
        <TouchableOpacity 
          style={styles.backButton}
          onPress={() => navigation.goBack()}
        >
          <Ionicons name="arrow-back" size={24} color="#ffffff" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Cari Observasi</Text>
      </View>
      
      <View style={styles.searchContainer}>
        <View style={styles.searchInputContainer}>
          <Ionicons name="search" size={20} color="#888888" style={styles.searchIcon} />
          <TextInput
            style={styles.searchInput}
            placeholder="Cari berdasarkan lokasi, tipe, dll."
            placeholderTextColor="#888888"
            value={searchQuery}
            onChangeText={setSearchQuery}
            returnKeyType="search"
            onSubmitEditing={handleSearch}
          />
          {searchQuery ? (
            <TouchableOpacity 
              style={styles.clearButton}
              onPress={() => setSearchQuery('')}
            >
              <Ionicons name="close-circle" size={20} color="#888888" />
            </TouchableOpacity>
          ) : null}
        </View>
        
        <TouchableOpacity 
          style={styles.searchButton}
          onPress={handleSearch}
        >
          <Text style={styles.searchButtonText}>Cari</Text>
        </TouchableOpacity>
      </View>
      
      <View style={styles.filterContainer}>
        <Text style={styles.filterText}>Filter:</Text>
        <View style={styles.filterOptions}>
          <TouchableOpacity style={styles.filterOption}>
            <Text style={styles.filterOptionText}>Semua</Text>
          </TouchableOpacity>
          <TouchableOpacity style={[styles.filterOption, styles.filterOptionInactive]}>
            <Text style={styles.filterOptionTextInactive}>Pemeliharaan</Text>
          </TouchableOpacity>
          <TouchableOpacity style={[styles.filterOption, styles.filterOptionInactive]}>
            <Text style={styles.filterOptionTextInactive}>Perburuan</Text>
          </TouchableOpacity>
        </View>
      </View>
      
      {loading ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#bf6420" />
        </View>
      ) : (
        <FlatList
          data={results}
          renderItem={({ item }) => (
            <ChecklistCard 
              checklist={item} 
              onPress={() => handleChecklistPress(item)} 
            />
          )}
          keyExtractor={(item) => item.id.toString()}
          contentContainerStyle={styles.resultsList}
          ListEmptyComponent={renderEmptyResult()}
        />
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
    alignItems: 'center',
    paddingHorizontal: 15,
    paddingTop: 50,
    paddingBottom: 15,
  },
  backButton: {
    padding: 5,
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#ffffff',
    marginLeft: 15,
  },
  searchContainer: {
    flexDirection: 'row',
    paddingHorizontal: 15,
    paddingBottom: 15,
  },
  searchInputContainer: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#222222',
    borderRadius: 8,
    paddingHorizontal: 10,
    marginRight: 10,
  },
  searchIcon: {
    marginRight: 10,
  },
  searchInput: {
    flex: 1,
    height: 40,
    color: '#ffffff',
    fontSize: 16,
  },
  clearButton: {
    padding: 5,
  },
  searchButton: {
    backgroundColor: '#bf6420',
    borderRadius: 8,
    justifyContent: 'center',
    paddingHorizontal: 15,
  },
  searchButtonText: {
    color: '#ffffff',
    fontWeight: 'bold',
  },
  filterContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 15,
    paddingBottom: 15,
  },
  filterText: {
    color: '#888888',
    marginRight: 10,
  },
  filterOptions: {
    flexDirection: 'row',
  },
  filterOption: {
    backgroundColor: '#bf6420',
    paddingVertical: 5,
    paddingHorizontal: 10,
    borderRadius: 15,
    marginRight: 8,
  },
  filterOptionInactive: {
    backgroundColor: '#333333',
  },
  filterOptionText: {
    color: '#ffffff',
    fontSize: 12,
  },
  filterOptionTextInactive: {
    color: '#888888',
    fontSize: 12,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  resultsList: {
    padding: 15,
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingTop: 100,
  },
  emptyText: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#ffffff',
    marginTop: 10,
  },
  emptySubText: {
    fontSize: 14,
    color: '#888888',
    textAlign: 'center',
    marginTop: 5,
    paddingHorizontal: 40,
  },
});

export default SearchChecklistScreen; 