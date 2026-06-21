## Overzicht

Dit project is een e-commerce webshop gebouwd met Laravel, Livewire en Filament. Het biedt een klantenwinkel met productcatalogus, winkelwagen en Stripe-checkout. Er is ook een beheerderspaneel via Filament voor beheer van producten, categorieën, rollen, gebruikers en bestellingen.

## Installatie

1. Clone de repository:
   ```bash
   git clone https://github.com/Zympoo/eindwerk_ecommerce.git
   cd eindwerk_ecommerce
   ```
2. Installeer PHP-afhankelijkheden:
   ```bash
   composer install
   ```
3. Kopieer de voorbeeldomgeving en genereer een app key:
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```
4. Configureer de database in `.env`.
   - Standaard gebruikt de app MySQL:
     - `DB_CONNECTION=mysql`
     - `DB_HOST=127.0.0.1`
     - `DB_DATABASE=eindwerk_ecommerce`
     - `DB_USERNAME=root`
     - `DB_PASSWORD=`
5. Voer migraties en seeders uit:
   ```bash
   php artisan migrate --seed
   ```
6. Installeer frontend-afhankelijkheden en bouw de assets:
   ```bash
   npm install
   npm run build
   ```
7. Start de ontwikkelserver:
   ```bash
   composer run dev
   ```

## Noodzakelijke configuratie

Voeg Stripe-keys toe aan `.env` voor de checkout-functionaliteit:

```env
STRIPE_PUBLIC_KEY=your_stripe_public_key
STRIPE_SECRET_KEY=your_stripe_secret_key
```

Als Stripe niet is geconfigureerd, zijn bestellingen en betalingstransacties niet volledig bruikbaar.

## Login-gegevens

De database seeders maken minstens één beheerdersaccount aan:

- Email: `admin@admin.com`
- Wachtwoord: `password`

Er worden daarnaast klantenaccounts aangemaakt met een standaardwachtwoord `password`, maar de e-mailadressen worden gegenereerd.

## Functionaliteiten

### Klantzijde

- Productcatalogus met categorieën
- Productdetailpagina's
- Winkelwagenbeheer
- Checkout met Stripe Payment
- Bestelgeschiedenis en orderdetailpagina's
- Inloggen en registreren via Livewire

### Beheerzijde (Filament)

- Adminpaneel beschikbaar op `/admin`
- Beheer van producten
- Beheer van categorieën
- Beheer van gebruikers
- Overzicht en bewerking van bestellingen

## Gebruikte technologieën

- PHP 8.3
- Laravel 13.8
- Livewire 4
- Filament 5
- Stripe PHP SDK
- MySQL
- Vite

## Demo instructies

1. Ga naar `http://127.0.0.1:8000/products`
2. Blader door producten en categorieën
3. Voeg een product toe aan de winkelwagen
4. Ga naar `/cart` en klik op `Checkout`
5. Vul adresgegevens in en druk op de knop `Proceed to payment`
6. Rond de betaling af via Stripe met kaartnummer `4242424242424242`
7. Bekijk bestellingen op `/orders`

Voor admin-toegang:

1. Ga naar `http://127.0.0.1:8000/admin`
2. Log in met de admin-gegevens
3. Beheer producten, categorieën, gebruikers en bestellingen

## Bronvermelding

- Laravel: https://laravel.com
- Livewire: https://livewire.laravel.com/
- Filament: https://filamentphp.com
- Stripe PHP SDK: https://docs.stripe.com/
 
