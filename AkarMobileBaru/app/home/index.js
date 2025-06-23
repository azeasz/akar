import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

export default function HomePage() {
  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <View>
          <Text style={styles.greeting}>Halo, Pengguna!</Text>
          <Text style={styles.subGreeting}>Selamat datang di AKAR</Text>
        </View>
        <TouchableOpacity style={styles.profileButton}>
          <Ionicons name="person-circle" size={40} color="#bf6420" />
        </TouchableOpacity>
      </View>

      <View style={styles.statsContainer}>
        <View style={styles.statCard}>
          <Text style={styles.statNumber}>5</Text>
          <Text style={styles.statLabel}>Observasi</Text>
        </View>
        <View style={styles.statCard}>
          <Text style={styles.statNumber}>12</Text>
          <Text style={styles.statLabel}>Spesies</Text>
        </View>
        <View style={styles.statCard}>
          <Text style={styles.statNumber}>3</Text>
          <Text style={styles.statLabel}>Lokasi</Text>
        </View>
      </View>

      <View style={styles.actionsContainer}>
        <Text style={styles.sectionTitle}>Aksi Cepat</Text>
        <View style={styles.actionButtonsContainer}>
          <TouchableOpacity style={styles.actionButton}>
            <Ionicons name="add-circle" size={24} color="#bf6420" />
            <Text style={styles.actionButtonText}>Observasi Baru</Text>
          </TouchableOpacity>
          <TouchableOpacity style={styles.actionButton}>
            <Ionicons name="camera" size={24} color="#bf6420" />
            <Text style={styles.actionButtonText}>Tambah Foto</Text>
          </TouchableOpacity>
          <TouchableOpacity style={styles.actionButton}>
            <Ionicons name="search" size={24} color="#bf6420" />
            <Text style={styles.actionButtonText}>Cari Data</Text>
          </TouchableOpacity>
        </View>
      </View>

      <View style={styles.recentContainer}>
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>Observasi Terbaru</Text>
          <TouchableOpacity>
            <Text style={styles.seeAllText}>Lihat Semua</Text>
          </TouchableOpacity>
        </View>

        {[1, 2, 3].map((item) => (
          <TouchableOpacity key={item} style={styles.checklistCard}>
            <View style={styles.cardHeader}>
              <View style={styles.typeTag}>
                <Text style={styles.typeText}>Pemeliharaan</Text>
              </View>
              <Text style={styles.dateText}>12 Juni 2023</Text>
            </View>
            <Text style={styles.locationText}>Lokasi Observasi #{item}</Text>
            <View style={styles.cardFooter}>
              <View style={styles.statsRow}>
                <Ionicons name="paw" size={16} color="#bf6420" />
                <Text style={styles.statsText}>4 Spesies</Text>
              </View>
              <View style={styles.statusTag}>
                <View style={styles.statusDot} />
                <Text style={styles.statusText}>Selesai</Text>
              </View>
            </View>
          </TouchableOpacity>
        ))}
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000000',
    padding: 16,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 20,
  },
  greeting: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  subGreeting: {
    fontSize: 16,
    color: '#cccccc',
  },
  profileButton: {
    padding: 5,
  },
  statsContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 20,
  },
  statCard: {
    flex: 1,
    backgroundColor: '#121212',
    borderRadius: 10,
    padding: 15,
    alignItems: 'center',
    marginHorizontal: 5,
    borderLeftWidth: 3,
    borderLeftColor: '#bf6420',
  },
  statNumber: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#bf6420',
  },
  statLabel: {
    fontSize: 14,
    color: '#ffffff',
    marginTop: 5,
  },
  actionsContainer: {
    marginBottom: 20,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#ffffff',
    marginBottom: 10,
  },
  actionButtonsContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  actionButton: {
    flex: 1,
    backgroundColor: '#121212',
    borderRadius: 10,
    padding: 15,
    alignItems: 'center',
    marginHorizontal: 5,
  },
  actionButtonText: {
    color: '#ffffff',
    marginTop: 5,
    fontSize: 12,
  },
  recentContainer: {
    marginBottom: 20,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  seeAllText: {
    color: '#bf6420',
    fontSize: 14,
  },
  checklistCard: {
    backgroundColor: '#121212',
    borderRadius: 10,
    padding: 15,
    marginBottom: 10,
    borderLeftWidth: 3,
    borderLeftColor: '#bf6420',
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 10,
  },
  typeTag: {
    backgroundColor: '#2ecc71',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  typeText: {
    color: '#ffffff',
    fontSize: 12,
    fontWeight: 'bold',
  },
  dateText: {
    color: '#888888',
    fontSize: 12,
  },
  locationText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#ffffff',
    marginBottom: 10,
  },
  cardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  statsRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  statsText: {
    color: '#cccccc',
    fontSize: 12,
    marginLeft: 5,
  },
  statusTag: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  statusDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: '#2ecc71',
    marginRight: 5,
  },
  statusText: {
    color: '#cccccc',
    fontSize: 12,
  },
});