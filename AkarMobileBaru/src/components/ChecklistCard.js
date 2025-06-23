import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

const ChecklistCard = ({ checklist, onPress }) => {
  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    });
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
      case 'perburuan': return 'md-compass';
      case 'lomba': return 'trophy';
      case 'perdagangan': return 'cart';
      case 'pemeliharaan': return 'home';
      case 'penangkaran': return 'leaf';
      case 'pemeliharaan & penangkaran': return 'paw';
      default: return 'list';
    }
  };

  return (
    <TouchableOpacity style={styles.card} onPress={() => onPress(checklist)}>
      <View style={styles.header}>
        <View style={[styles.typeTag, { backgroundColor: getTypeColor(checklist.type) }]}>
          <Ionicons name={getTypeIcon(checklist.type)} size={14} color="#ffffff" style={styles.typeIcon} />
          <Text style={styles.typeText}>{checklist.type}</Text>
        </View>
        <Text style={styles.date}>{formatDate(checklist.tanggal)}</Text>
      </View>
      
      <Text style={styles.location}>{checklist.nama_lokasi}</Text>
      
      <View style={styles.footer}>
        <View style={styles.statsContainer}>
          <View style={styles.statItem}>
            <Ionicons name="paw-outline" size={16} color="#bf6420" />
            <Text style={styles.statText}>
              {checklist.faunas?.length || 0} Spesies
            </Text>
          </View>
          
          <View style={styles.statItem}>
            <Ionicons name="people-outline" size={16} color="#bf6420" />
            <Text style={styles.statText}>
              {checklist.faunas?.reduce((sum, fauna) => sum + fauna.jumlah, 0) || 0} Individu
            </Text>
          </View>
        </View>
        
        <View style={styles.statusContainer}>
          <View style={[
            styles.statusDot, 
            { backgroundColor: checklist.is_completed ? '#2ecc71' : '#f39c12' }
          ]} />
          <Text style={styles.statusText}>
            {checklist.is_completed ? 'Selesai' : 'Draft'}
          </Text>
        </View>
      </View>
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  card: {
    backgroundColor: '#121212',
    borderRadius: 10,
    padding: 15,
    marginBottom: 15,
    borderLeftWidth: 3,
    borderLeftColor: '#bf6420',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  typeTag: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  typeIcon: {
    marginRight: 4,
  },
  typeText: {
    color: '#ffffff',
    fontSize: 12,
    fontWeight: 'bold',
  },
  date: {
    color: '#888888',
    fontSize: 12,
  },
  location: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 10,
  },
  footer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  statsContainer: {
    flexDirection: 'row',
  },
  statItem: {
    flexDirection: 'row',
    alignItems: 'center',
    marginRight: 15,
  },
  statText: {
    color: '#cccccc',
    fontSize: 12,
    marginLeft: 4,
  },
  statusContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  statusDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    marginRight: 5,
  },
  statusText: {
    color: '#cccccc',
    fontSize: 12,
  },
});

export default ChecklistCard; 