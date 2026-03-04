// FİTNESS101 Veri Dosyası

// Antrenman Programları
const programs = [
    {
        id: 1,
        name: "Güç Antrenmanı",
        category: "strength",
        description: "Kas inşası ve gücü artırmak için tasarlanmış program",
        features: [
            "Haftada 4 gün antrenman",
            "Kişi başına 1 saat",
            "Progresif yük artırma",
            "Beslenme planı dahil"
        ],
        difficulty: "Orta"
    },
    {
        id: 2,
        name: "HIIT Kardiyovasküler",
        category: "cardio",
        description: "Yüksek yoğunluk interval antrenmanı ile kalp sağlığı",
        features: [
            "Haftada 3 gün antrenman",
            "45 dakika seans",
            "Yağ yakma odaklı",
            "Pratik ve etkili"
        ],
        difficulty: "Zor"
    },
    {
        id: 3,
        name: "Yoga & Esneklik",
        category: "flexibility",
        description: "Esneklik ve zihinsel huzur için",
        features: [
            "Haftada 3 gün",
            "60 dakika seans",
            "Stres azaltma",
            "Tüm yaşlar için"
        ],
        difficulty: "Kolay"
    },
    {
        id: 4,
        name: "Kilo Vermek Programı",
        category: "weight",
        description: "Kontrollü kilo kaybı için holistik yaklaşım",
        features: [
            "Haftada 5 gün antrenman",
            "Beslenme koçluğu",
            "Ilerleme takibi",
            "Motivasyon desteği"
        ],
        difficulty: "Orta"
    },
    {
        id: 5,
        name: "Crossfit Gelişmiş",
        category: "strength",
        description: "Disfokusyonel fitness için ileri program",
        features: [
            "Haftada 4 gün",
            "60 dakika seans",
            "Teknik eğitim",
            "Grup desteği"
        ],
        difficulty: "Zor"
    },
    {
        id: 6,
        name: "Pilates Çekirdek",
        category: "flexibility",
        description: "Çekirdek kuvveti ve duruş iyileştirmesi",
        features: [
            "Haftada 3 gün",
            "50 dakika seans",
            "Sakinleştirici vücut",
            "Yaralanma önlemesi"
        ],
        difficulty: "Orta"
    }
];

// Paketler
const packages = [
    {
        id: 1,
        name: "Başlangıç",
        price: 299,
        duration: "1 Ay",
        features: [
            "Tesiste erişim",
            "Temel ekipman kullanımı",
            "E-posta desteği",
            "Ilerleme izleme",
            "Beslenme rehberi"
        ],
        disabled: []
    },
    {
        id: 2,
        name: "Profesyonel",
        price: 599,
        duration: "1 Ay",
        features: [
            "Sınırsız tesiste erişim",
            "Kişisel antrenman seansları",
            "Beslenme danışmanlığı",
            "Grup dersleri",
            "24/7 desteği",
            "Canlı sınıflar"
        ],
        disabled: [],
        featured: true
    },
    {
        id: 3,
        name: "Premium",
        price: 899,
        duration: "1 Ay",
        features: [
            "VIP tesiste erişim",
            "Hafta 3 kişisel antrenman",
            "Beslenme ve sağlık koçluğu",
            "Tüm grup dersleri",
            "Öncelikli destek",
            "Spa ve sauna erişim",
            "Mobil uygulama pro"
        ],
        disabled: []
    }
];

// Sık Sorulan Sorular
const faqs = [
    {
        question: "FİTNESS101 nedir?",
        answer: "FİTNESS101, 2015 yılından beri hizmet veren modern bir fitness merkezidir. Profesyonel antrenörlük, beslenme danışmanlığı ve son teknoloji ekipmanlar sunuyoruz."
    },
    {
        question: "Yeni başlayanlar için uygun mu?",
        answer: "Evet, bizim programlarımız tüm seviyelere uygun. Yeni başlayanlardan ileri seviyelere kadar programlarımız vardır. Profesyonel antrenörlerimiz size rehberlik edecektir."
    },
    {
        question: "Üyeliği ne zaman iptal edebilirim?",
        answer: "Aylık paketlerde hiçbir bağlılık yoktur. İstediğiniz zaman iptal edebilirsiniz. Üç aylık ve yıllık paketlerde belirli şartlar geçerlidir."
    },
    {
        question: "Çalışma saatleri nelerdir?",
        answer: "Pazartesi-Cuma saat 06:00-23:00, Cumartesi-Pazar saat 08:00-22:00 da açıktayız. Tatil günlerinde saat 10:00-18:00 de hizmet verilir."
    },
    {
        question: "Beslenme planlaması yapılıyor mu?",
        answer: "Evet, tüm paketlerde beslenme desteği dahil. Premium paketlerde kişiye özel beslenme koçluğu yapılır."
    },
    {
        question: "Grup dersleri ne zaman yapılıyor?",
        answer: "Grup dersleri haftada 30+ oturum sunulmaktadır. Yoga, Pilates, HIIT, Zumba ve daha pek çok derse katılabilirsiniz. Website'den program görebilirsiniz."
    }
];

// Takım Üyeleri
const team = [
    {
        id: 1,
        name: "Ahmet Yılmaz",
        position: "Baş Antrenör",
        specialty: "Güç Antrenmanı"
    },
    {
        id: 2,
        name: "Ayşe Demir",
        position: "Beslenme Uzmanı",
        specialty: "Beslenme Koçluğu"
    },
    {
        id: 3,
        name: "Mehmet Kaya",
        position: "Yoga Eğitmeni",
        specialty: "Yoga & Esneklik"
    },
    {
        id: 4,
        name: "Fatma Şahin",
        position: "HIIT Antrenörü",
        specialty: "Kardiyovasküler Antrenman"
    }
];

// İletişim Bilgileri
const contactInfo = {
    address: "Merkez Mah. Fitness Cad. No: 123, İstanbul",
    phone: "(0212) 555-0123",
    email: "info@fitness101.com",
    hours: {
        weekday: "06:00 - 23:00",
        weekend: "08:00 - 22:00"
    }
};

// Başarı Hikayeleri
const successStories = [
    {
        id: 1,
        name: "Ali Kaya",
        before: "85kg",
        after: "72kg",
        timeframe: "6 Ay",
        image: "👨‍💼"
    },
    {
        id: 2,
        name: "Zehra Yıldız",
        before: "Çok zayıf",
        after: "Fit & Güçlü",
        timeframe: "4 Ay",
        image: "👩‍💼"
    }
];
