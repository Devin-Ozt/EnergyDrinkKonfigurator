/**
 * Energy Pulver Dose Konfigurator - JavaScript
 */

// Größen-Preisliste
const sizePrices = {
    300: 14.95,
    400: 18.95,
    500: 22.95
};

// Konfigurationsstatus
const config = {
    currentStep: 1,
    totalSteps: 6,
    
    // Ausgewählte Optionen
    size: null,
    caffeine: null,
    flavors: [],
    additives: [],
    sweetener: null,
    canColor: '#00ff88',
    canName: '',
    
    // Preise
    sizePrice: 0,
    caffeinePrice: 0,
    flavorsPrice: 0,
    additivesPrice: 0,
    sweetenerPrice: 0,
    
    // Gutschein
    couponCode: null,
    couponDiscount: 0
};

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    initializeKonfigurator();
});

/**
 * Initialisiert den Konfigurator
 */
function initializeKonfigurator() {
    // Event Listener für Size Cards
    document.querySelectorAll('.option-card[data-type="size"]').forEach(card => {
        card.addEventListener('click', function() {
            handleSizeSelection(this);
        });
    });
    
    // Event Listener für Option Cards (Koffein, Süßung)
    document.querySelectorAll('.option-card[data-type="caffeine"], .option-card[data-type="sweetener"]').forEach(card => {
        card.addEventListener('click', function() {
            handleSingleSelection(this);
        });
    });
    
    // Event Listener für Flavor Cards (Multi-Select)
    document.querySelectorAll('.flavor-card').forEach(card => {
        card.addEventListener('click', function() {
            handleFlavorSelection(this);
        });
    });
    
    // Event Listener für Additive Cards (Multi-Select)
    document.querySelectorAll('.option-card[data-type="additive"]').forEach(card => {
        card.addEventListener('click', function() {
            handleAdditiveSelection(this);
        });
    });
    
    // Flavor-Suche
    const flavorSearch = document.getElementById('flavor-search');
    if (flavorSearch) {
        flavorSearch.addEventListener('input', filterFlavors);
    }
    
    // Filter-Buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filterFlavors();
        });
    });
    
    // Farb-Presets
    document.querySelectorAll('.color-preset').forEach(preset => {
        preset.addEventListener('click', function() {
            const color = this.dataset.color;
            document.getElementById('can-color').value = color;
            config.canColor = color;
            updateCanPreview();
            
            document.querySelectorAll('.color-preset').forEach(p => p.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
    
    // Farbwähler
    const colorPicker = document.getElementById('can-color');
    if (colorPicker) {
        colorPicker.addEventListener('input', function() {
            config.canColor = this.value;
            updateCanPreview();
        });
    }
    
    // Name Input
    const nameInput = document.getElementById('can-name');
    if (nameInput) {
        nameInput.addEventListener('input', function() {
            config.canName = this.value;
            updateCanPreview();
        });
    }
    
    // Step-Klicks
    document.querySelectorAll('.step').forEach(step => {
        step.addEventListener('click', function() {
            const stepNum = parseInt(this.dataset.step);
            if (stepNum <= config.currentStep || this.classList.contains('completed')) {
                goToStep(stepNum);
            }
        });
    });
    
    // Preset laden falls vorhanden
    if (typeof presetData !== 'undefined' && presetData.preset) {
        loadPreset(presetData);
    }
    
    // Initial Update
    updatePriceDisplay();
}

/**
 * Behandelt Größen-Auswahl
 */
function handleSizeSelection(card) {
    const size = parseInt(card.dataset.size);
    const price = parseFloat(card.dataset.price);
    
    // Alle anderen Karten deselektieren
    document.querySelectorAll('.option-card[data-type="size"]').forEach(c => {
        c.classList.remove('selected');
    });
    
    // Diese Karte selektieren
    card.classList.add('selected');
    card.classList.add('pop');
    setTimeout(() => card.classList.remove('pop'), 300);
    
    config.size = size;
    config.sizePrice = price;
    
    // Size Text im Preis-Display aktualisieren
    const sizeRow = document.getElementById('price-size');
    if (sizeRow) {
        sizeRow.querySelector('span:first-child').textContent = `Dose (${size}g)`;
        sizeRow.querySelector('span:last-child').textContent = formatPrice(price);
    }
    
    // Size im Vorschau-SVG aktualisieren
    const sizeText = document.getElementById('can-size-text');
    if (sizeText) {
        sizeText.textContent = size + 'g';
    }
    
    updatePriceDisplay();
}

/**
 * Behandelt Single-Selection (Koffein, Süßung)
 */
function handleSingleSelection(card) {
    const type = card.dataset.type;
    const id = parseInt(card.dataset.id);
    const price = parseFloat(card.dataset.price);
    const name = card.dataset.name;
    
    // Alle anderen Karten deselektieren
    card.closest('.options-grid').querySelectorAll('.option-card').forEach(c => {
        c.classList.remove('selected');
    });
    
    // Diese Karte selektieren
    card.classList.add('selected');
    card.classList.add('pop');
    setTimeout(() => card.classList.remove('pop'), 300);
    
    if (type === 'caffeine') {
        config.caffeine = { id, name, price, mg: card.dataset.mg };
        config.caffeinePrice = price;
    } else if (type === 'sweetener') {
        config.sweetener = { id, name, price };
        config.sweetenerPrice = price;
    }
    
    updatePriceDisplay();
    updateCanPreview();
}

/**
 * Behandelt Flavor-Auswahl (max. 3)
 */
function handleFlavorSelection(card) {
    const id = parseInt(card.dataset.id);
    const price = parseFloat(card.dataset.price);
    const name = card.dataset.name;
    const color = card.dataset.color;
    
    const existingIndex = config.flavors.findIndex(f => f.id === id);
    
    if (existingIndex !== -1) {
        // Flavor entfernen
        config.flavors.splice(existingIndex, 1);
        card.classList.remove('selected');
    } else if (config.flavors.length < 3) {
        // Flavor hinzufügen
        config.flavors.push({ id, name, price, color });
        card.classList.add('selected');
        card.classList.add('pop');
        setTimeout(() => card.classList.remove('pop'), 300);
    } else {
        // Max erreicht - Shake Animation
        card.classList.add('shake');
        setTimeout(() => card.classList.remove('shake'), 500);
        return;
    }
    
    // Flavor-Preis berechnen
    config.flavorsPrice = config.flavors.reduce((sum, f) => sum + f.price, 0);
    
    // Counter aktualisieren
    const counter = document.getElementById('flavor-count');
    if (counter) {
        counter.textContent = `${config.flavors.length} / 3 Flavors gewählt`;
        counter.className = 'badge ' + (config.flavors.length === 0 ? 'bg-secondary' : 
                                        config.flavors.length < 3 ? 'bg-info' : 'bg-success');
    }
    
    updatePriceDisplay();
    updateCanPreview();
    updateFlavorsPreview();
}

/**
 * Behandelt Additive-Auswahl
 */
function handleAdditiveSelection(card) {
    const id = parseInt(card.dataset.id);
    const price = parseFloat(card.dataset.price);
    const name = card.dataset.name;
    
    const existingIndex = config.additives.findIndex(a => a.id === id);
    
    if (existingIndex !== -1) {
        config.additives.splice(existingIndex, 1);
        card.classList.remove('selected');
    } else {
        config.additives.push({ id, name, price });
        card.classList.add('selected');
        card.classList.add('pop');
        setTimeout(() => card.classList.remove('pop'), 300);
    }
    
    config.additivesPrice = config.additives.reduce((sum, a) => sum + a.price, 0);
    
    updatePriceDisplay();
}

/**
 * Filtert Flavors nach Suche und Kategorie
 */
function filterFlavors() {
    const searchTerm = document.getElementById('flavor-search')?.value.toLowerCase() || '';
    const activeFilter = document.querySelector('.filter-btn.active')?.dataset.filter || 'all';
    
    document.querySelectorAll('.flavor-card').forEach(card => {
        const name = card.dataset.name.toLowerCase();
        const category = card.dataset.category;
        
        const matchesSearch = name.includes(searchTerm);
        const matchesFilter = activeFilter === 'all' || category === activeFilter;
        
        card.style.display = (matchesSearch && matchesFilter) ? 'block' : 'none';
    });
}

/**
 * Aktualisiert die Preisanzeige
 */
function updatePriceDisplay() {
    const caffeineRow = document.getElementById('price-caffeine');
    const flavorsRow = document.getElementById('price-flavors');
    const additivesRow = document.getElementById('price-additives');
    const sweetenerRow = document.getElementById('price-sweetener');
    const discountRow = document.getElementById('price-discount');
    const totalEl = document.getElementById('total-price');
    
    // Koffein
    if (caffeineRow) {
        if (config.caffeinePrice > 0) {
            caffeineRow.style.display = 'flex';
            caffeineRow.querySelector('span:last-child').textContent = formatPrice(config.caffeinePrice);
        } else {
            caffeineRow.style.display = 'none';
        }
    }
    
    // Flavors
    if (flavorsRow) {
        if (config.flavorsPrice > 0) {
            flavorsRow.style.display = 'flex';
            flavorsRow.querySelector('span:last-child').textContent = formatPrice(config.flavorsPrice);
        } else {
            flavorsRow.style.display = 'none';
        }
    }
    
    // Zusätze
    if (additivesRow) {
        if (config.additivesPrice > 0) {
            additivesRow.style.display = 'flex';
            additivesRow.querySelector('span:last-child').textContent = formatPrice(config.additivesPrice);
        } else {
            additivesRow.style.display = 'none';
        }
    }
    
    // Süßung
    if (sweetenerRow) {
        if (config.sweetenerPrice > 0) {
            sweetenerRow.style.display = 'flex';
            sweetenerRow.querySelector('span:last-child').textContent = formatPrice(config.sweetenerPrice);
        } else {
            sweetenerRow.style.display = 'none';
        }
    }
    
    // Gesamtpreis berechnen
    let total = config.sizePrice + config.caffeinePrice + config.flavorsPrice + 
                config.additivesPrice + config.sweetenerPrice;
    
    // Rabatt anwenden
    let discount = 0;
    if (config.couponDiscount > 0) {
        discount = total * (config.couponDiscount / 100);
        total -= discount;
        
        if (discountRow) {
            discountRow.style.display = 'flex';
            discountRow.querySelector('span:last-child').textContent = '-' + formatPrice(discount);
        }
    } else if (discountRow) {
        discountRow.style.display = 'none';
    }
    
    if (totalEl) {
        totalEl.textContent = formatPrice(total);
    }
}

/**
 * Aktualisiert die Dosen-Vorschau
 */
function updateCanPreview() {
    // Farbe
    const color = config.canColor;
    const darkerColor = adjustColor(color, -30);
    
    document.getElementById('gradient-stop-1')?.setAttribute('style', `stop-color:${color};stop-opacity:1`);
    document.getElementById('gradient-stop-2')?.setAttribute('style', `stop-color:${adjustColor(color, 20)};stop-opacity:1`);
    document.getElementById('gradient-stop-3')?.setAttribute('style', `stop-color:${color};stop-opacity:1`);
    document.getElementById('can-bottom')?.setAttribute('fill', darkerColor);
    
    // Name
    const nameText = document.getElementById('can-name-text');
    if (nameText) {
        nameText.textContent = config.canName || 'DEIN NAME';
    }
    
    // Koffein
    const caffeineText = document.getElementById('can-caffeine-text');
    if (caffeineText && config.caffeine) {
        caffeineText.textContent = config.caffeine.mg + 'mg';
    }
}

/**
 * Aktualisiert die Flavor-Vorschau
 */
function updateFlavorsPreview() {
    const container = document.getElementById('selected-flavors-preview');
    if (!container) return;
    
    if (config.flavors.length === 0) {
        container.innerHTML = '<small class="text-muted">Noch keine Flavors gewählt</small>';
    } else {
        container.innerHTML = config.flavors.map(f => 
            `<span class="badge me-1" style="background: ${f.color}; color: ${getContrastColor(f.color)}">${f.name}</span>`
        ).join('');
    }
}

/**
 * Geht zum nächsten Schritt
 */
function nextStep() {
    if (config.currentStep < config.totalSteps) {
        // Validierung
        if (config.currentStep === 1 && !config.size) {
            alert('Bitte wähle eine Dosengröße aus.');
            return;
        }
        if (config.currentStep === 2 && !config.caffeine) {
            alert('Bitte wähle ein Koffein-Level aus.');
            return;
        }
        if (config.currentStep === 3 && config.flavors.length === 0) {
            alert('Bitte wähle mindestens einen Geschmack aus.');
            return;
        }
        if (config.currentStep === 5 && !config.sweetener) {
            alert('Bitte wähle ein Süßungsmittel aus.');
            return;
        }
        
        // Aktuellen Schritt als completed markieren
        document.querySelector(`.step[data-step="${config.currentStep}"]`).classList.add('completed');
        
        goToStep(config.currentStep + 1);
    }
}

/**
 * Geht zum vorherigen Schritt
 */
function prevStep() {
    if (config.currentStep > 1) {
        goToStep(config.currentStep - 1);
    }
}

/**
 * Wechselt zu einem bestimmten Schritt
 */
function goToStep(step) {
    // Alle Steps deaktivieren
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.step-content').forEach(c => c.classList.remove('active'));
    
    // Neuen Step aktivieren
    document.querySelector(`.step[data-step="${step}"]`).classList.add('active');
    document.getElementById(`step-${step}`).classList.add('active');
    
    config.currentStep = step;
    
    // Scroll nach oben
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Geht zur Zusammenfassung
 */
function goToSummary() {
    // Validierung
    if (!config.size) {
        alert('Bitte wähle eine Dosengröße aus.');
        goToStep(1);
        return;
    }
    if (!config.caffeine) {
        alert('Bitte wähle ein Koffein-Level aus.');
        goToStep(2);
        return;
    }
    if (config.flavors.length === 0) {
        alert('Bitte wähle mindestens einen Geschmack aus.');
        goToStep(3);
        return;
    }
    if (!config.sweetener) {
        alert('Bitte wähle ein Süßungsmittel aus.');
        goToStep(5);
        return;
    }
    
    // Gesamtpreis berechnen
    let total = config.sizePrice + config.caffeinePrice + config.flavorsPrice + 
                config.additivesPrice + config.sweetenerPrice;
    
    if (config.couponDiscount > 0) {
        total -= total * (config.couponDiscount / 100);
    }
    
    // Konfiguration für die Zusammenfassung
    const summaryData = {
        size: config.size,
        caffeine: config.caffeine,
        flavors: config.flavors,
        additives: config.additives,
        sweetener: config.sweetener,
        canColor: config.canColor,
        canName: config.canName || 'Mein Mix',
        prices: {
            size: config.sizePrice,
            caffeine: config.caffeinePrice,
            flavors: config.flavorsPrice,
            additives: config.additivesPrice,
            sweetener: config.sweetenerPrice,
            discount: config.couponDiscount,
            total: total
        },
        coupon: config.couponCode
    };
    
    // Daten in Form einfügen und absenden
    document.getElementById('config-data').value = JSON.stringify(summaryData);
    document.getElementById('summary-form').submit();
}

/**
 * Gutschein anwenden
 */
async function applyCoupon() {
    const codeInput = document.getElementById('coupon-code');
    const code = codeInput.value.trim().toUpperCase();
    
    if (!code) return;
    
    const successEl = document.getElementById('coupon-success');
    const errorEl = document.getElementById('coupon-error');
    const messageEl = document.getElementById('coupon-message');
    
    // Beide verstecken
    successEl.style.display = 'none';
    errorEl.style.display = 'none';
    
    try {
        const response = await fetch('api/check_coupon.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code })
        });
        
        const result = await response.json();
        
        if (result.success) {
            config.couponCode = code;
            config.couponDiscount = result.discount;
            messageEl.textContent = `${result.discount}% Rabatt angewendet!`;
            successEl.style.display = 'block';
            codeInput.disabled = true;
            updatePriceDisplay();
        } else {
            errorEl.style.display = 'block';
        }
    } catch (e) {
        errorEl.style.display = 'block';
    }
}

/**
 * Preset laden
 */
function loadPreset(data) {
    if (!data.preset) return;
    
    // Koffein-Level setzen
    const caffeineCard = document.querySelector(`.option-card[data-type="caffeine"][data-id="${data.preset.caffeine_level_id}"]`);
    if (caffeineCard) {
        handleSingleSelection(caffeineCard);
    }
    
    // Süßungsmittel setzen
    const sweetenerCard = document.querySelector(`.option-card[data-type="sweetener"][data-id="${data.preset.sweetener_id}"]`);
    if (sweetenerCard) {
        handleSingleSelection(sweetenerCard);
    }
    
    // Flavors setzen
    data.flavors.forEach(flavorId => {
        const flavorCard = document.querySelector(`.flavor-card[data-id="${flavorId}"]`);
        if (flavorCard) {
            handleFlavorSelection(flavorCard);
        }
    });
    
    // Additives setzen
    data.additives.forEach(additiveId => {
        const additiveCard = document.querySelector(`.option-card[data-type="additive"][data-id="${additiveId}"]`);
        if (additiveCard) {
            handleAdditiveSelection(additiveCard);
        }
    });
    
    // Farbe setzen
    if (data.preset.dosen_farbe) {
        config.canColor = data.preset.dosen_farbe;
        document.getElementById('can-color').value = data.preset.dosen_farbe;
        updateCanPreview();
    }
}

// === Hilfsfunktionen ===

/**
 * Formatiert einen Preis
 */
function formatPrice(price) {
    return price.toFixed(2).replace('.', ',') + ' €';
}

/**
 * Passt eine Farbe an (heller/dunkler)
 */
function adjustColor(color, amount) {
    const hex = color.replace('#', '');
    const r = Math.max(0, Math.min(255, parseInt(hex.substr(0, 2), 16) + amount));
    const g = Math.max(0, Math.min(255, parseInt(hex.substr(2, 2), 16) + amount));
    const b = Math.max(0, Math.min(255, parseInt(hex.substr(4, 2), 16) + amount));
    return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
}

/**
 * Berechnet Kontrastfarbe
 */
function getContrastColor(hexcolor) {
    const hex = hexcolor.replace('#', '');
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);
    const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
    return (yiq >= 128) ? '#000000' : '#ffffff';
}
