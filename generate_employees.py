import csv
import random

# Daftar data master yang disesuaikan dengan database terbaru (HC Database.sql)
organizations = [
    "Finance & Accounting", "Human Capital", "Maintenance", "General Affair", 
    "Sales Support", "Sales 1", "Sales 2", "Internal Control", 
    "Quality Assurance & R D", "Process Engineering", "Procurement", 
    "Production Planning", "Plant A", "Plant B", "Plant C", "Plant D", 
    "Plant E", "Legal", "Marketing", "Facility", "Information Technology", 
    "Commercial & Supply Chain"
]

positions = [
    "Teknisi Elektrik", "Manager Accounting", "Supervisor", "Komandan Security", 
    "Account Receivable", "Electrical Engineer", "Operator Konstruksi", 
    "Operator Mesin", "Sopir Forklift", "Manager IT"
]

levels = [
    "Manager", "Supervisor", "Staff", "Foreman", 
    "Director", "Jr. Staff", "Staff Ahli"
]

statuses = ["Active", "Inactive"]

# Daftar nama acak untuk simulasi
first_names = ["Andi", "Budi", "Siti", "Rudi", "Dewi", "Eko", "Fajar", "Gita", "Hendra", "Indah", 
               "Joko", "Kartika", "Lia", "Maman", "Nina", "Oki", "Putra", "Rina", "Samsul", "Tanti"]
last_names = ["Wijaya", "Santoso", "Aminah", "Hermawan", "Saputra", "Lestari", "Kusuma", "Pratama", 
              "Sari", "Hidayat", "Nugroho", "Purnomo", "Wati", "Zulkarnain", "Ramadhan"]

file_name = "data_karyawan_terupdate_500.csv"

with open(file_name, mode="w", newline="", encoding="utf-8") as file:
    writer = csv.writer(file)
    # Header sesuai permintaan
    writer.writerow(["NIK", "Nama Lengkap", "Organization", "Position", "Level", "Status"])
    
    # Generate 500 baris
    for i in range(1, 501):
        nik = f"EMP{i:04d}" # Menggunakan format EMP0001 dst agar unik
        nama = f"{random.choice(first_names)} {random.choice(last_names)}"
        org = random.choice(organizations)
        pos = random.choice(positions)
        lvl = random.choice(levels)
        status = random.choices(statuses, weights=[90, 10])[0] # 90% Active
        
        writer.writerow([nik, nama, org, pos, lvl, status])

print(f"Berhasil membuat file: {file_name}")