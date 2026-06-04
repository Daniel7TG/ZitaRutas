#!/usr/bin/env python3
"""
Script para optimizar puntos de navegación de ZitaRutas.
Reduce la densidad de puntos manteniendo la forma de las rutas.
"""

import psycopg2
import math
import json
from pathlib import Path

DB_CONFIG = {
    'host': '127.0.0.1',
    'port': 5432,
    'database': 'zita_rutas',
    'user': 'postgres',
    'password': 'postgres'
}
MIN_DISTANCE_M =140  # Distancia mínima entre puntos (metros)
DOUGLAS_PEUCKER_EPSILON = 0.01 # Tolerancia para simplificación

def haversine(lat1, lon1, lat2, lon2):
    """Calcula distancia en metros entre dos puntos."""
    R = 6371000
    phi1 = math.radians(lat1)
    phi2 = math.radians(lat2)
    delta_phi = math.radians(lat2 - lat1)
    delta_lambda = math.radians(lon2 - lon1)
    
    a = math.sin(delta_phi/2)**2 + math.cos(phi1) * math.cos(phi2) * math.sin(delta_lambda/2)**2
    c = 2 * math.atan2(math.sqrt(a), math.sqrt(1-a))
    
    return R * c

def perpendicular_distance(point, line_start, line_end):
    """Distancia perpendicular de un punto a una línea."""
    if line_start == line_end:
        return haversine(point[0], point[1], line_start[0], line_start[1])
    
    lat1, lon1 = line_start
    lat2, lon2 = line_end
    lat, lon = point
    
    # Convertir a coordenadas planas aproximadas
    x1, y1 = lon1 * 111320 * math.cos(math.radians(lat1)), lat1 * 110540
    x2, y2 = lon2 * 111320 * math.cos(math.radians(lat2)), lat2 * 110540
    x, y = lon * 111320 * math.cos(math.radians(lat)), lat * 110540
    
    dx, dy = x2 - x1, y2 - y1
    if dx == 0 and dy == 0:
        return math.sqrt((x - x1)**2 + (y - y1)**2)
    
    t = ((x - x1) * dx + (y - y1) * dy) / (dx**2 + dy**2)
    t = max(0, min(1, t))
    
    nearest_x = x1 + t * dx
    nearest_y = y1 + t * dy
    
    return math.sqrt((x - nearest_x)**2 + (y - nearest_y)**2)

def douglas_peucker(points, epsilon):
    """Algoritmo Douglas-Peucker para simplificar polilíneas."""
    if len(points) <= 2:
        return points
    
    dmax = 0
    index = 0
    
    for i in range(1, len(points) - 1):
        d = perpendicular_distance(points[i], points[0], points[-1])
        if d > dmax:
            index = i
            dmax = d
    
    if dmax > epsilon:
        left = douglas_peucker(points[:index+1], epsilon)
        right = douglas_peucker(points[index:], epsilon)
        return left[:-1] + right
    else:
        return [points[0], points[-1]]

def reduce_by_distance(points, min_distance):
    """Reduce puntos manteniendo distancia mínima."""
    if len(points) <= 2:
        return points
    
    result = [points[0]]
    
    for i in range(1, len(points) - 1):
        last = result[-1]
        dist = haversine(last[0], last[1], points[i][0], points[i][1])
        if dist >= min_distance:
            result.append(points[i])
    
    result.append(points[-1])
    return result

def process_routes():
    """Procesa todas las rutas y genera puntos optimizados."""
    conn = psycopg2.connect(**DB_CONFIG)
    cursor = conn.cursor()
    
    # Obtener todas las rutas
    cursor.execute("SELECT id FROM rutas")
    rutas = [row[0] for row in cursor.fetchall()]
    
    total_original = 0
    total_optimizado = 0
    
    # Limpiar tabla de paradas optimizadas
    cursor.execute("DELETE FROM paradas_optimizadas")
    
    for ruta_id in rutas:
        # Obtener puntos de la ruta ordenados
        cursor.execute("""
            SELECT id, latitud, longitud 
            FROM puntos_navegacion 
            WHERE ruta_id = %s 
            ORDER BY id
        """, (ruta_id,))
        
        puntos = [(float(row[1]), float(row[2])) for row in cursor.fetchall()]
        total_original += len(puntos)
        
        if len(puntos) < 3:
            # Ruta muy corta, mantener todos los puntos
            puntos_optimizados = puntos
        else:
            # Aplicar Douglas-Peucker primero
            puntos_simplificados = douglas_peucker(puntos, DOUGLAS_PEUCKER_EPSILON)
            
            # Luego reducir por distancia mínima
            puntos_optimizados = reduce_by_distance(puntos_simplificados, MIN_DISTANCE_M)
        
        total_optimizado += len(puntos_optimizados)
        
        # Insertar puntos optimizados
        for orden, (lat, lng) in enumerate(puntos_optimizados):
            cursor.execute("""
                INSERT INTO paradas_optimizadas (ruta_id, latitud, longitud, orden, created_at, updated_at)
                VALUES (%s, %s, %s, %s, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            """, (ruta_id, lat, lng, orden))
        
        print(f"Ruta {ruta_id}: {len(puntos)} -> {len(puntos_optimizados)} puntos")
    
    conn.commit()
    conn.close()
    
    print(f"\n{'='*50}")
    print(f"Total original: {total_original} puntos")
    print(f"Total optimizado: {total_optimizado} puntos")
    if total_original > 0:
        print(f"Reducción: {100 * (1 - total_optimizado/total_original):.1f}%")
    else:
        print("Reducción: 0.0%")
    print(f"{'='*50}")

if __name__ == "__main__":
    print("Optimizando puntos de navegación...")
    print(f"Distancia mínima: {MIN_DISTANCE_M}m")
    print(f"Tolerancia Douglas-Peucker: {DOUGLAS_PEUCKER_EPSILON}")
    print()
    process_routes()
    print("\n¡Optimización completada!")
