// FİTNESS101 Ana JavaScript Dosyası

document.addEventListener('DOMContentLoaded', function() {
    loadPrograms();
    loadDetailedPrograms();
    loadPricing();
    loadFAQ();
    loadTeam();
    setupFilters();
    setupContactForm();
});

// ============= Programları Yükleme =============
function loadPrograms() {
    const programsGrid = document.getElementById('programsGrid');
    if (!programsGrid) return;
    
    programsGrid.innerHTML = programs.map(program => `
        <div class="program-card">
            <div class="program-header">
                <h3>${program.name}</h3>
                <span class="program-badge">${program.difficulty}</span>
            </div>
            <div class="program-body">
                <p>${program.description}</p>
                <ul class="program-features">
                    ${program.features.map(feature => `<li>${feature}</li>`).join('')}
                </ul>
                <a href="contact.html" class="btn btn-primary">İleti Gönder</a>
            </div>
        </div>
    `).join('');
}

// Detaylı programlar sayfası için
function loadDetailedPrograms(filter = 'all') {
    const grid = document.getElementById('detailedProgramsGrid');
    if (!grid) return;
    
    let filtered = programs;
    if (filter !== 'all') {
        filtered = programs.filter(p => p.category === filter);
    }
    
    grid.innerHTML = filtered.map(program => `
        <div class="program-card" data-category="${program.category}">
            <div class="program-header">
                <h3>${program.name}</h3>
                <span class="program-badge">${program.difficulty}</span>
            </div>
            <div class="program-body">
                <p>${program.description}</p>
                <ul class="program-features">
                    ${program.features.map(feature => `<li>${feature}</li>`).join('')}
                </ul>
                <a href="contact.html" class="btn btn-primary">Başla</a>
            </div>
        </div>
    `).join('');
}

// ============= Paketleri Yükleme =============
function loadPricing() {
    const pricingGrid = document.getElementById('pricingGrid');
    if (!pricingGrid) return;
    
    pricingGrid.innerHTML = packages.map(pkg => `
        <div class="pricing-card ${pkg.featured ? 'featured' : ''}">
            <div class="pricing-header">
                <h3>${pkg.name}</h3>
                <div class="pricing-price">
                    ${pkg.price}₺
                    <span>/${pkg.duration}</span>
                </div>
                <p class="pricing-duration">Üyelik süresi: ${pkg.duration}</p>
            </div>
            <div class="pricing-body">
                <ul class="pricing-features">
                    ${pkg.features.map(feature => `
                        <li class="${pkg.disabled.includes(feature) ? 'disabled' : ''}">
                            ${feature}
                        </li>
                    `).join('')}
                </ul>
                <button class="btn btn-primary" onclick="enrollPackage('${pkg.name}', ${pkg.price})">
                    Üye Ol
                </button>
            </div>
        </div>
    `).join('');
}

// ============= FAQ Yükleme =============
function loadFAQ() {
    const faqItems = document.getElementById('faqItems');
    if (!faqItems) return;
    
    faqItems.innerHTML = faqs.map((faq, index) => `
        <div class="faq-item" data-index="${index}">
            <div class="faq-question" onclick="toggleFAQ(this)">
                <span>${faq.question}</span>
                <span class="faq-arrow">+</span>
            </div>
            <div class="faq-answer">
                <p>${faq.answer}</p>
            </div>
        </div>
    `).join('');
}

// FAQ Aç/Kapat
function toggleFAQ(element) {
    const item = element.parentElement;
    const isActive = item.classList.contains('active');
    
    // Tüm açık öğeleri kapat
    document.querySelectorAll('.faq-item.active').forEach(el => {
        if (el !== item) el.classList.remove('active');
    });
    
    // Geçerli öğeyi aç/kapat
    item.classList.toggle('active');
}

// ============= Takım Üyeleri Yükleme =============
function loadTeam() {
    const teamGrid = document.getElementById('teamGrid');
    if (!teamGrid) return;
    
    teamGrid.innerHTML = team.map(member => `
        <div class="team-card">
            <div class="team-image">👨‍🏫</div>
            <div class="team-info">
                <h3>${member.name}</h3>
                <p>${member.position}</p>
                <p style="font-size: 0.85rem; color: var(--primary-color);">${member.specialty}</p>
            </div>
        </div>
    `).join('');
}

// ============= Filter Ayarla =============
function setupFilters() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Tüm butonlardan active sınıfını kaldır
            filterBtns.forEach(b => b.classList.remove('active'));
            // Tıklanan butona active sınıfı ekle
            this.classList.add('active');
            
            // Filter'ı uygula
            const filter = this.getAttribute('data-filter');
            loadDetailedPrograms(filter);
        });
    });
}

// ============= İletişim Formu =============
function setupContactForm() {
    const form = document.getElementById('contactForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = form.querySelector('input[type="text"]').value;
        const email = form.querySelector('input[type="email"]').value;
        const phone = form.querySelector('input[type="tel"]').value;
        const message = form.querySelector('textarea').value;
        
        // Basit validasyon
        if (!name || !email || !message) {
            alert('Lütfen tüm zorunlu alanları doldurunuz!');
            return;
        }
        
        // Başarı mesajı
        alert(`Teşekkürler ${name}! Mesajınız alındı. En kısa sürede sizinle iletişime geçeceğiz.`);
        form.reset();
    });
}

// ============= Paket Olma =============
function enrollPackage(packageName, price) {
    alert(`${packageName} paketini seçtiniz (${price}₺). Ödeme sayfasına yönlendirileceksiniz...`);
    // Gerçek bir uygulamada, burası ödeme sayfasına yönlendirecek
}

// ============= Smooth Scroll =============
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

// ============= Local Storage ile Veriler Kaydet =============
function saveContactMessage(name, email, phone, message) {
    let messages = localStorage.getItem('contactMessages');
    if (!messages) messages = '[]';
    
    const msgArray = JSON.parse(messages);
    msgArray.push({
        name,
        email,
        phone,
        message,
        date: new Date().toLocaleString('tr-TR')
    });
    
    localStorage.setItem('contactMessages', JSON.stringify(msgArray));
}

// ============= Responsive Menu (Mobil) =============
function toggleMobileMenu() {
    const navLinks = document.querySelector('.nav-links');
    navLinks.style.display = navLinks.style.display === 'none' ? 'flex' : 'none';
}

// ============= Sayfa Yüklendiğinde Animasyonlar =============
window.addEventListener('load', function() {
    // Sayfa yüklenme animasyonu
    document.querySelectorAll('.feature-card, .program-card, .pricing-card, .team-card').forEach((el, index) => {
        el.style.animation = `fadeInUp 0.6s ease-out ${index * 0.1}s both`;
    });
});

// CSS Animasyonları ekle
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);

// Scroll üzerine efekt
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 100) {
        navbar.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.15)';
    } else {
        navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
    }
});
