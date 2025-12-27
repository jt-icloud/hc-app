import csv
import random

# 1. Referensi ID Valid dari Database Anda
ORG_IDS = [2, 4, 5, 6, 7, 8, 9, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25]
LEVEL_IDS = [1, 2, 5, 6, 7, 8, 9]
POSITION_IDS = [1, 2, 3, 5, 6, 7, 8, 9, 10, 11]
STATUSES = ['Active', 'Inactive']

# 2. Data Nama untuk Variasi (Agar terlihat profesional)
first_names = ['Andi', 'Budi', 'Candra', 'Dedi', 'Eko', 'Fajar', 'Guntur', 'Hendra', 'Indra', 'Joko', 'Kurniawan', 'Lutfi', 'Mulyono', 'Nugroho', 'Oki', 'Prabowo', 'Rian', 'Sutrisno', 'Taufik', 'Umar']
last_names = ['Saputra', 'Wijaya', 'Kusuma', 'Santoso', 'Hidayat', 'Pratama', 'Setiawan', 'Ramadhan', 'Gunawan', 'Sari', 'Putri', 'Lestari', 'Utami', 'Wulandari']

def generate_name():
    return f"{random.choice(first_names)} {random.choice(last_names)}"

# 3. Proses Pembuatan File CSV
filename = 'employees_500_rows.csv'

with open(filename, mode='w', newline='', encoding='utf-8') as file:
    writer = csv.writer(file)
    
    # Menulis Header (Sesuai struktur tabel employees)
    writer.writerow(['employee_id', 'full_name', 'org_id', 'position_id', 'level_id', 'status'])
    
    for i in range(1, 501):
        emp_id = f"EMP{str(i).zfill(4)}" # Hasil: EMP0001, EMP0002, dst.
        full_name = generate_name()
        org_id = random.choice(ORG_IDS)
        pos_id = random.choice(POSITION_IDS)
        lvl_id = random.choice(LEVEL_IDS)
        # 90% Active, 10% Inactive
        status = random.choices(STATUSES, weights=[90, 10])[0]
        
        writer.writerow([emp_id, full_name, org_id, pos_id, lvl_id, status])

print(f"Sukses! File '{filename}' telah dibuat dengan 500 baris data.")