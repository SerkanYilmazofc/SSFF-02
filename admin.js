// FİTNESS101 Admin Paneli JavaScript

document.addEventListener('DOMContentLoaded', function() {
    loadProgramsList();
    loadPackagesList();
    loadStats();
});

// ============= Programlar Yönetimi =============
function addProgram() {
    const name = document.getElementById('programName').value;
    const category = document.getElementById('programCategory').value;
    const description = document.getElementById('programDescription').value;
    const difficulty = document.getElementById('programDifficulty').value;
    
    if (!name || !description) {
        alert('Lütfen tüm alanları doldurunuz!');
        return;
    }
    
    const newProgram = {
        id: programs.length + 1,
        name,
        category,
        description,
        difficulty,
        features: [
            "Esnek antrenman programı",
            "Profesyonel rehberlik",
            "Ilerleme takibi",
            "Detaylı beslenme planı"
        ]
    };
    
    programs.push(newProgram);
    saveData();
    clearProgramForm();
    loadProgramsList();
    loadStats();
    alert('Program başarıyla eklendi!');
}

function clearProgramForm() {
    document.getElementById('programName').value = '';
    document.getElementById('programCategory').value = 'strength';
    document.getElementById('programDescription').value = '';
    document.getElementById('programDifficulty').value = 'Orta';
}

function loadProgramsList() {
    const tbody = document.getElementById('programsList');
    tbody.innerHTML = programs.map(program => `
        <tr>
            <td>${program.name}</td>
            <td>${getCategoryName(program.category)}</td>
            <td>${program.difficulty}</td>
            <td>
                <button class="btn btn-small btn-danger" onclick="deleteProgram(${program.id})">Sil</button>
            </td>
        </tr>
    `).join('');
}

function deleteProgram(id) {
    if (confirm('Bu programı silmek istediğinizden emin misiniz?')) {
        const index = programs.findIndex(p => p.id === id);
        if (index !== -1) {
            programs.splice(index, 1);
            saveData();
            loadProgramsList();
            loadStats();
            alert('Program silindi!');
        }
    }
}

function getCategoryName(category) {
    const categories = {
        strength: 'Güç',
        cardio: 'Kardiyovasküler',
        flexibility: 'Esneklik',
        weight: 'Kilo Vermek'
    };
    return categories[category] || category;
}

// ============= Paketler Yönetimi =============
function addPackage() {
    const name = document.getElementById('packageName').value;
    const price = parseFloat(document.getElementById('packagePrice').value);
    const duration = document.getElementById('packageDuration').value;
    
    if (!name || !price || !duration) {
        alert('Lütfen tüm alanları doldurunuz!');
        return;
    }
    
    const newPackage = {
        id: packages.length + 1,
        name,
        price,
        duration,
        features: [
            "Tesiste sınırsız erişim",
            "Kişisel antrenman desteği",
            "Beslenme danışmanlığı",
            "Grup dersleri"
        ],
        disabled: []
    };
    
    packages.push(newPackage);
    saveData();
    clearPackageForm();
    loadPackagesList();
    loadStats();
    alert('Paket başarıyla eklendi!');
}

function clearPackageForm() {
    document.getElementById('packageName').value = '';
    document.getElementById('packagePrice').value = '';
    document.getElementById('packageDuration').value = '';
}

function loadPackagesList() {
    const tbody = document.getElementById('packagesList');
    tbody.innerHTML = packages.map(pkg => `
        <tr>
            <td>${pkg.name}</td>
            <td>${pkg.price}₺</td>
            <td>${pkg.duration}</td>
            <td>
                <button class="btn btn-small btn-danger" onclick="deletePackage(${pkg.id})">Sil</button>
            </td>
        </tr>
    `).join('');
}

function deletePackage(id) {
    if (confirm('Bu paketi silmek istediğinizden emin misiniz?')) {
        const index = packages.findIndex(p => p.id === id);
        if (index !== -1) {
            packages.splice(index, 1);
            saveData();
            loadPackagesList();
            loadStats();
            alert('Paket silindi!');
        }
    }
}

// ============= İletişim Mesajları =============
function loadContactMessages() {
    const messages = localStorage.getItem('contactMessages');
    const tbody = document.getElementById('messagesList');
    
    if (!messages) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Henüz mesaj yok</td></tr>';
        return;
    }
    
    const msgArray = JSON.parse(messages);
    if (msgArray.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Henüz mesaj yok</td></tr>';
        return;
    }
    
    tbody.innerHTML = msgArray.map((msg, index) => `
        <tr>
            <td>${msg.name}</td>
            <td>${msg.email}</td>
            <td>${msg.phone || '-'}</td>
            <td>${msg.message.substring(0, 50)}...</td>
            <td>${msg.date}</td>
            <td>
                <button class="btn btn-small btn-danger" onclick="deleteMessage(${index})">Sil</button>
            </td>
        </tr>
    `).join('');
    
    document.getElementById('statsMessages').textContent = msgArray.length;
}

function deleteMessage(index) {
    if (confirm('Bu mesajı silmek istediğinizden emin misiniz?')) {
        let messages = localStorage.getItem('contactMessages');
        if (messages) {
            const msgArray = JSON.parse(messages);
            msgArray.splice(index, 1);
            localStorage.setItem('contactMessages', JSON.stringify(msgArray));
            loadContactMessages();
        }
    }
}

function clearAllMessages() {
    if (confirm('Tüm mesajları silmek istediğinizden emin misiniz?')) {
        localStorage.removeItem('contactMessages');
        loadContactMessages();
        alert('Tüm mesajlar silindi!');
    }
}

// ============= Site Ayarları =============
function saveSettings() {
    const settings = {
        title: document.getElementById('siteTitle').value,
        description: document.getElementById('siteDescription').value,
        phone: document.getElementById('contactPhone').value,
        email: document.getElementById('contactEmail').value
    };
    
    localStorage.setItem('siteSettings', JSON.stringify(settings));
    alert('Ayarlar kaydedildi!');
}

function resetSettings() {
    document.getElementById('siteTitle').value = 'FİTNESS101';
    document.getElementById('siteDescription').value = 'Sağlıklı yaşama ilk adımı atın';
    document.getElementById('contactPhone').value = '(0212) 555-0123';
    document.getElementById('contactEmail').value = 'info@fitness101.com';
    
    localStorage.removeItem('siteSettings');
    alert('Ayarlar sıfırlandı!');
}

// ============= Veri Yönetimi =============
function exportData() {
    const data = {
        programs,
        packages,
        team,
        faqs,
        exportDate: new Date().toLocaleString('tr-TR')
    };
    
    const dataStr = JSON.stringify(data, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    const url = URL.createObjectURL(dataBlob);
    
    const link = document.createElement('a');
    link.href = url;
    link.download = 'fitness101-veriler.json';
    link.click();
    
    alert('Veriler başarıyla indirildi!');
}

function importData() {
    const fileInput = document.getElementById('importFile');
    fileInput.click();
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = function(event) {
            try {
                const data = JSON.parse(event.target.result);
                
                if (data.programs) programs = data.programs;
                if (data.packages) packages = data.packages;
                if (data.team) team = data.team;
                if (data.faqs) faqs = data.faqs;
                
                saveData();
                loadProgramsList();
                loadPackagesList();
                loadStats();
                
                alert('Veriler başarıyla yüklendi!');
            } catch (error) {
                alert('Dosya yüklenirken hata oluştu!');
            }
        };
        reader.readAsText(file);
    });
}

// ============= İstatistikler =============
function loadStats() {
    document.getElementById('statsPrograms').textContent = programs.length;
    document.getElementById('statsPackages').textContent = packages.length;
    
    const messages = localStorage.getItem('contactMessages');
    const messageCount = messages ? JSON.parse(messages).length : 0;
    document.getElementById('statsMessages').textContent = messageCount;
}

// ============= Local Storage İşlemleri =============
function saveData() {
    localStorage.setItem('fitnessPrograms', JSON.stringify(programs));
    localStorage.setItem('fitnessPackages', JSON.stringify(packages));
    localStorage.setItem('fitnessTeam', JSON.stringify(team));
    localStorage.setItem('fitnessFAQs', JSON.stringify(faqs));
}

// Sayfaya giriş sırasında kontrol et (şifre olmadan basit kontrol)
function checkAdminAccess() {
    const password = prompt('Admin Paneline hoşgeldiniz! Şifre giriniz:');
    if (password !== 'admin123') {
        alert('Yanlış şifre!');
        window.location.href = 'index.html';
    }
}

// Sayfa yüklendiğinde mesajları yükle
window.addEventListener('load', function() {
    loadContactMessages();
});
