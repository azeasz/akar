import React from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, TextInput } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';

const dummyData = [
  {
    id: '1',
    type: 'Pemeliharaan',
    nama_lokasi: 'Unknown place',
    tanggal: '2023-06-01',
    is_completed: true,
    faunas: [
      { id: '1', nama_spesies: 'Elang Jawa', jumlah: 1 },
    ]
  },
  {
    id: '2',
    type: 'Perburuan',
    nama_lokasi: 'Unknown place',
    tanggal: '2023-06-01',
    is_completed: false,
    faunas: [
      { id: '3', nama_spesies: 'Orangutan', jumlah: 2 },
    ]
  },
  {
    id: '3',
    type: 'Penangkaran',
    nama_lokasi: 'Unknown place',
    tanggal: '2023-05-31',
    is_completed: true,
    faunas: [
      { id: '4', nama_spesies: 'Rusa', jumlah: 2 },
    ]
  },
  {
    id: '4',
    type: 'Penangkaran',
    nama_lokasi: 'Unknown place',
    tanggal: '2023-05-31',
    is_completed: true,
    faunas: [
      { id: '5', nama_spesies: 'Babi Hutan', jumlah: 2 },
    ]
  },
  {
    id: '5',
    type: 'Penangkaran',
    nama_lokasi: 'Unknown place',
    tanggal: '2023-05-31',
    is_completed: true,
    faunas: [
      { id: '6', nama_spesies: 'Burung Merak', jumlah: 2 },
    ]
  },
  {
    id: '6',
    type: 'Pemeliharaan',
    nama_lokasi: 'Unknown place',
    tanggal: '2023-05-31',
    is_completed: true,
    faunas: [
      { id: '7', nama_spesies: 'Jalak Bali', jumlah: 2 },
    ]
  },
  {
    id: '7',
    type: 'Pemeliharaan',
    nama_lokasi: 'Unknown place',
    tanggal: '2023-05-31',
    is_completed: true,
    faunas: [
      { id: '8', nama_spesies: 'Kakaktua', jumlah: 2 },
    ]
  },
];

const ChecklistItem = ({ item, onPress }) => {
  const formatDate = (dateString) => {
    const date = new Date(dateString);
    const month = date.toLocaleString('id-ID', { month: 'short' }).toUpperCase();
    const day = date.getDate();
    return { day, month };
  };

  const getTypeColor = (type) => {
    switch (type.toLowerCase()) {
      case 'perburuan': return '#e74c3c';
      case 'lomba': return '#3498db';
      case 'perdagangan': return '#f39c12';
      case 'pemeliharaan': return '#2ecc71';
      case 'penangkaran': return '#9b59b6';
      case 'pemeliharaan & penangkaran': return '#1abc9c';
      default: return '#95a5a6';
    }
  };

  const getTypeIcon = (type) => {
    switch (type.toLowerCase()) {
      case 'perburuan': return 'target';
      case 'lomba': return 'trophy';
      case 'perdagangan': return 'cart';
      case 'pemeliharaan': return 'home';
      case 'penangkaran': return 'paw';
      default: return 'list';
    }
  };

  const { day, month } = formatDate(item.tanggal);
  const totalSpecies = item.faunas.length;
  const totalIndividuals = item.faunas.reduce((sum, fauna) => sum + fauna.jumlah, 0);

  return (
    <TouchableOpacity style={styles.itemContainer} onPress={() => onPress(item)}>
      <View style={styles.iconAndContent}>
        <View style={[styles.iconContainer, { backgroundColor: getTypeColor(item.type) }]}>
          <Ionicons name={getTypeIcon(item.type)} size={24} color="#ffffff" />
        </View>
        
        <View style={styles.contentContainer}>
          <Text style={styles.typeText}>{item.type}</Text>
          <Text style={styles.locationText}>{item.nama_lokasi}</Text>
          <Text style={styles.statsText}>{totalSpecies} sp | {totalIndividuals} ind</Text>
        </View>
      </View>
      
      <View style={styles.dateContainer}>
        <Text style={styles.dayText}>{day}</Text>
        <Text style={styles.monthText}>{month}</Text>
      </View>
    </TouchableOpacity>
  );
};

export default function ChecklistsScreen() {
  const handleChecklistPress = (checklist) => {
    router.push(`/checklist/${checklist.id}`);
  };

  return (
    <View style={styles.container}>
      <View style={styles.searchContainer}>
        <Ionicons name="search" size={20} color="#888" style={styles.searchIcon} />
        <TextInput
          style={styles.searchInput}
          placeholder="Cari"
          placeholderTextColor="#888"
        />
        <View style={styles.viewButtons}>
          <TouchableOpacity style={styles.viewButton}>
            <Ionicons name="grid-outline" size={22} color="#bf6420" />
          </TouchableOpacity>
          <TouchableOpacity style={styles.viewButton}>
            <Ionicons name="list-outline" size={22} color="#888" />
          </TouchableOpacity>
        </View>
      </View>
      
      <FlatList
        data={dummyData}
        keyExtractor={(item) => item.id}
        renderItem={({ item }) => (
          <ChecklistItem item={item} onPress={handleChecklistPress} />
        )}
        contentContainerStyle={styles.listContainer}
      />
      
      <TouchableOpacity style={styles.fab}>
        <Ionicons name="add" size={24} color="#fff" />
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000000',
    padding: 16,
  },
  searchContainer: {
    flexDirection: 'row',
    backgroundColor: '#1a1a1a',
    borderRadius: 10,
    paddingHorizontal: 10,
    paddingVertical: 8,
    marginBottom: 16,
    alignItems: 'center',
  },
  searchIcon: {
    marginRight: 10,
  },
  searchInput: {
    flex: 1,
    color: '#ffffff',
    fontSize: 16,
  },
  viewButtons: {
    flexDirection: 'row',
  },
  viewButton: {
    padding: 5,
    marginLeft: 5,
  },
  listContainer: {
    paddingBottom: 80,
  },
  itemContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    borderBottomWidth: 0.5,
    borderBottomColor: '#333',
    paddingVertical: 12,
  },
  iconAndContent: {
    flexDirection: 'row',
    flex: 1,
  },
  iconContainer: {
    width: 50,
    height: 50,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  contentContainer: {
    justifyContent: 'center',
    flex: 1,
  },
  typeText: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#ffffff',
    marginBottom: 2,
  },
  locationText: {
    fontSize: 14,
    color: '#888888',
    marginBottom: 4,
  },
  statsText: {
    fontSize: 12,
    color: '#666666',
  },
  dateContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingLeft: 10,
  },
  dayText: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#bf6420',
  },
  monthText: {
    fontSize: 14,
    color: '#bf6420',
  },
  fab: {
    position: 'absolute',
    bottom: 20,
    right: 20,
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: '#bf6420',
    justifyContent: 'center',
    alignItems: 'center',
    elevation: 5,
  },
});