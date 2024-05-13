# Subscription Application Managment

Merhaba,

Bu proje mobil uygulamadan her cihazın sisteme kaydolmasını ve kaydolan cihazın belirtilen uygulama üstünden satın alma işlemi yaparak abone olmasını ve aboneliğin takip edilmesini amaçlıyor.

## Kullanılan Teknolojiler

- Laravel 11
- PHP 8.2
- MongoDB
- Redis
- Docker

## Kurulum Talimatları

Projeyi çalıştırmak için Docker gereklidir. İlgili teknolojileri Docker üzerinde çalıştırmak için Docker Compose kullanabilirsiniz. Aşağıdaki komutları izleyerek projeyi başlatabilirsiniz:

1. **Depoyu Klonlayın:**
    ```bash
    git clone https://github.com/cuneyt/subscription-managment-app.git
    ```
2. **Docker Container'ları Başlatın:**  
   Projenin bulunduğu dizine gidin ve Docker Compose dosyasını çalıştırın:
    ```bash
    docker-compose up -d
    ```

## API Endpointleri ve Kullanım Detayları

### Kayıt Olma

**Endpoint:** `/register`

**Açıklama:** Cihazın sisteme kaydolmasını sağlar.

**Gerekli Parametreler:**

- `uid` (Integer): Cihazın eşsiz kimlik numarası.
- `AppId` (Integer): Uygulama kimliği, her bir uygulamaya ait cihazlar için kullanılır.
- `os` (String): Cihazın işletim sistemi. Örnek: "Apple".
- `language` (String): Cihazın dili. Örnek: "Tr".

### Satın Alma

**Endpoint:** `/purchase`

**Açıklama:** Sistemdeki bir uygulama için satın alma işlemi yapar. Satın alma işlemi için gönderilen receipt değeri hash'lenerek son karakteri tek sayı ise satın alma olumlu sonuçlanır, çift sayı ise Giriş Sağlanamadı hatası döner. 

**Gerekli Parametreler:**

- `client-token` (String): `/register` endpoint'inden elde edilen token.
- `receipt` (String): Satın alma işlemini gerçekleştiren string değeri.

### Aboneliği Uzatma

**Endpoint:** `/renewed`

**Açıklama:** Abonelik expired-date'ini 240 dakika olarak artırır ve abonelik durumunu true olarak işaretler.

**Gerekli Parametreler:**

- `client-token` (String): `/register` endpoint'inden elde edilen token.
- `receipt` (String): Satın alma işlemini gerçekleştiren string değeri.

### Abonelik Kontrolü

**Endpoint:** `/checksubscription`

**Açıklama:** Belirtilen client-token için abonelik olup olmadığını kontrol eder.

**Gerekli Parametreler:**

- `client-token` (String): `/register` endpoint'inden elde edilen token.

### Abonelikleri Sonlandırma

**Endpoint:** `/worker`

**Açıklama:** Bu endpoint'e herhangi bir değer göndermenize gerek yoktur, istek atıldığı zaman aktif aboneliklerin expired-date'lerine bakar ve eğer geçmiş zamana ait bir abonelik bulursa bunu iptal eder. Bu endpoint'e her 30 dakikada bir cronjob isteği atılmaktadır. 


![Docker Logo](https://www.docker.com/wp-content/uploads/2023/08/logo-guide-logos-1.svg)  
![Laravel Logo](https://picperf.io/https://laravelnews.s3.amazonaws.com/images/laravel-featured.png)  
![MongoDB Logo](https://webassets.mongodb.com/_com_assets/cms/mongodb-logo-rgb-j6w271g1xn.jpg)  
![Redis Logo](https://redis.io/wp-content/uploads/2024/04/Logotype.svg)
