<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Калькулятор тарифов</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <h1 class="calculator-title">Калькулятор тарифов</h1>

            <form id="calculatorForm">
                @csrf
                <div class="region-selector">
                    <label class="region-label" for="region-select">Укажите регион передвижения</label>
                    <div class="region-value">
                        <select id="region-select" name="region" class="region-select">
                            <option value="1">Регион 1 (макс. 1200 тонн)</option>
                            <option value="2">Регион 2 (макс. 800 тонн)</option>
                            <option value="3">Регион 3 (макс. 500 тонн)</option>
                        </select>
                        <img src="{{ asset('assets/images/img_vector_2.svg') }}" alt="Dropdown arrow" class="dropdown-arrow">
                    </div>
                </div>

                <div class="volume-selector">
                    <div class="volume-label">Прокачка</div>
                    <div class="volume-value"><span id="volume-display">200</span> тонн</div>
                    <div class="slider-container">
                        <div class="slider">
                            <div class="slider-fill"></div>
                            <div class="slider-thumb">
                                <div class="slider-thumb-inner"></div>
                            </div>
                        </div>
                    </div>
                    <div class="slider-labels">
                        <span>0 тонн</span>
                        <span>250 тонн</span>
                        <span>500+ тонн</span>
                    </div>
                    <input type="hidden" name="pumping" id="pumping" value="200">
                </div>

                <div class="fuel-type-tabs">
                    <div class="fuel-tab active" data-value="petrol">Бензин</div>
                    <div class="fuel-tab" data-value="gas">Газ</div>
                    <div class="fuel-tab" data-value="diesel">ДТ</div>
                    <input type="hidden" name="fuelType" id="fuelType" value="petrol">
                </div>

                <div class="section-title">Укажите любимый бренд</div>
                <div class="brand-selector" id="brand-selector">
                    <!-- Brands will be dynamically populated -->
                </div>
                <input type="hidden" name="brand" id="brand">

                <div class="section-title">Дополнительные услуги</div>
                <div class="services-grid" id="services-grid">
                    <!-- Services will be dynamically populated -->
                </div>
                <input type="hidden" name="services" id="services">
            </form>
        </div>

        <div class="right-panel">
            <div class="tariff-card">
                <div class="tariff-header">
                    <div class="tariff-label">Подходящий тариф</div>
                    <div class="tariff-badge">
                        <div class="tariff-badge-star"></div>
                        <div class="tariff-badge-text">Избранный</div>
                    </div>
                </div>

                <div class="card-container">

                </div>

                <div class="gas-station-info">
                    <img src="{{ asset('assets/images/img_group.svg') }}" alt="Gas station icon" class="gas-station-icon">
                    <div class="gas-station-text">Сеть АЗС на карте</div>
                    <div class="gas-station-line"></div>
                </div>

                <div class="horizontal-line"></div>

                <div class="promo-section">
                    <div class="promo-title">Выберите промо-акцию:</div>
                    <div class="promo-options" id="promo-options">
                        <!-- Promo options will be dynamically populated -->
                    </div>
                    <input type="hidden" name="promo" id="promo">
                </div>

                <div class="horizontal-line"></div>

                <div class="savings-section">
                    <div class="savings-title">Ваша экономия:</div>
                    <div class="savings-columns">
                        <div class="savings-column">
                            <div class="savings-label">экономия в год</div>
                            <div class="savings-value" id="yearly-savings">от 34 млн ₽</div>
                        </div>
                        <div class="savings-separator"></div>
                        <div class="savings-column">
                            <div class="savings-label">экономия в месяц</div>
                            <div class="savings-value" id="monthly-savings">от 1 700 000 ₽</div>
                        </div>
                    </div>
                </div>

                <button class="order-button" id="submit-button">
                    Заказать тариф «Избранный»
                    <img src="{{ asset('assets/images/img_vector_9x19.svg') }}" alt="Arrow icon" class="order-button-arrow">
                </button>
            </div>
        </div>
    </div>

    <!-- Add popup HTML structure before closing body tag -->
    <div class="modal-overlay" id="orderModal">
        <div class="modal">
            <button class="close-button" aria-label="Закрыть"></button>
            <h1 class="modal-title">Заказать тариф «Избранный»</h1>

            <div class="form-container">
                <input type="text" class="input-field" id="inn" placeholder="Номер ИНН">
                <div class="error-message" id="inn-error">Пожалуйста, введите корректный ИНН</div>

                <input type="tel" class="input-field" id="phone" placeholder="Телефон для связи">
                <div class="error-message" id="phone-error">Пожалуйста, введите корректный номер телефона</div>

                <input type="email" class="input-field" id="email" placeholder="E-mail для связи">
                <div class="error-message" id="email-error">Пожалуйста, введите корректный email</div>

                <label class="checkbox-container">
                    <div class="custom-checkbox" id="consent-checkbox">
                        <span class="checkmark"></span>
                    </div>
                    <span class="checkbox-text">Согласен с обработкой персональных данных</span>
                    <input type="checkbox" id="consent" style="display: none;">
                </label>
                <div class="error-message" id="consent-error">Необходимо согласие на обработку персональных данных</div>

                <button class="submit-button" id="submit-btn">Заказать тариф «Избранный»</button>
            </div>
        </div>
    </div>

    <script>
        const brands = {
            petrol: [
                { name: 'Shell', logo: 'img_vector.svg' },
                { name: 'Газпром', logo: 'img_group_82.svg' },
                { name: 'Роснефть', logo: 'img_group_38.svg' },
                { name: 'Татнефть', logo: 'img_group_81.svg' },
                { name: 'Лукойл', logo: 'img_rect2493.svg' },
                { name: 'Башнефть', logo: 'img_ellipse_11.png' }
            ],
            gas: [
                { name: 'Shell', logo: 'img_vector.svg' },
                { name: 'Газпром', logo: 'img_group_82.svg' },
                { name: 'Башнефть', logo: 'img_ellipse_11.png' }
            ],
            diesel: [
                { name: 'Татнефть', logo: 'img_group_81.svg' },
                { name: 'Лукойл', logo: 'img_rect2493.svg' }
            ]
        };

        const services = [
            { name: 'Штрафы', icon: 'img_group_84.svg' },
            { name: 'Парковки', icon: 'parking' },
            { name: 'ЭДО', icon: 'img_rectangle_44.svg' },
            { name: 'Мойки', icon: 'img_ellipse_9.svg' },
            { name: 'Отсрочка', icon: 'delay' },
            { name: 'Телематика', icon: 'img_group_85.svg' },
            { name: 'PPRPAY', icon: 'img_group_86.svg' },
            { name: 'СМС', icon: 'img_rectangle_44_36x32.svg' },
            { name: 'Страховка', icon: 'img_vector_35x27.svg' }
        ];

        const promos = {
            'Эконом': [2, 5],
            'Избранный': [5, 20],
            'Премиум': [20, 50]
        };

        function updateBrands() {
            const fuelType = $('#fuelType').val();
            const brandSelector = $('#brand-selector');
            brandSelector.empty();

            brands[fuelType].forEach((brand, index) => {
                const isActive = index === 0 ? 'active' : '';
                brandSelector.append(`
                    <div class="brand-item">
                        <div class="brand-circle ${isActive}">
                            <img src="{{ asset('assets/images/${brand.logo}') }}" alt="${brand.name} logo">
                        </div>
                        <div class="brand-name ${isActive}">${brand.name}</div>
                    </div>
                `);
            });
            $('#brand').val(brands[fuelType][0].name);
        }

        function updateServices() {
            const servicesGrid = $('#services-grid');
            servicesGrid.empty();

            services.forEach((service, index) => {
                const isActive = index === 0 ? 'active' : '';
                let iconHtml = '';

                if (service.icon === 'parking') {
                    iconHtml = `
                        <div style="width: 32px; height: 32px; border: 3px solid #ffffff; border-radius: 7px; position: relative;">
                            <div style="width: 9px; height: 11px; border: 3px solid #ffffff; border-radius: 3px; position: absolute; top: 10px; left: 10px;"></div>
                            <div style="width: 14px; height: 3px; background-color: #ffffff; position: absolute; top: 10px; left: 10px;"></div>
                        </div>
                    `;
                } else if (service.icon === 'delay') {
                    iconHtml = `
                        <div style="width: 30px; height: 30px; border: 3px solid #d6d6d6; border-radius: 15px; position: relative;">
                            <div style="width: 3px; height: 9px; background-color: #d6d6d6; position: absolute; top: 13px; left: 14px;"></div>
                            <div style="width: 9px; height: 3px; background-color: #d6d6d6; position: absolute; top: 7px; left: 14px;"></div>
                        </div>
                    `;
                } else {
                    iconHtml = `<img src="{{ asset('assets/images/${service.icon}') }}" alt="${service.name} icon">`;
                }

                servicesGrid.append(`
                    <div class="service-item">
                        <div class="service-circle ${isActive}">
                            ${iconHtml}
                        </div>
                        <div class="service-name ${isActive}">${service.name}</div>
                    </div>
                `);
            });
        }

        function updatePromo(tariff) {
            const promoOptions = $('#promo-options');
            promoOptions.empty();

            promos[tariff].forEach((value, index) => {
                const isActive = index === 0 ? 'active' : '';
                promoOptions.append(`
                    <div class="promo-option">
                        <div class="promo-circle ${isActive}">
                            <div class="promo-check">
                                <img src="{{ asset('assets/images/img_vector_13.svg') }}" alt="Check icon" class="promo-check-icon">
                            </div>
                            <div class="promo-value ${isActive}">${value}%</div>
                        </div>
                        <div class="promo-description ${isActive}">${getPromoDescription(value)}</div>
                    </div>
                `);
            });
            $('#promo').val(promos[tariff][0]);
        }

        function getPromoDescription(value) {
            switch(value) {
                case 50: return 'Экономии на штрафах';
                case 20: return 'Возврат НДС';
                case 5: return 'Скидка на мойку';
                case 2: return 'Скидка на топливо';
                default: return '';
            }
        }

        function getTariff(fuelType, pumping) {
            if (fuelType === 'petrol') {
                if (pumping < 100) return 'Эконом';
                if (pumping < 300) return 'Избранный';
                return 'Премиум';
            } else if (fuelType === 'gas') {
                if (pumping < 200) return 'Эконом';
                if (pumping < 700) return 'Избранный';
                return 'Премиум';
            } else if (fuelType === 'diesel') {
                if (pumping < 150) return 'Эконом';
                if (pumping < 350) return 'Избранный';
                return 'Премиум';
            }
        }

        // Event Listeners
        $('.fuel-tab').on('click', function() {
            $('.fuel-tab').removeClass('active');
            $(this).addClass('active');
            let value = $(this).data('value');
            $('#fuelType').val(value);
            updateBrands();
            calculateSavings();
        });

        $('#brand-selector').on('click', '.brand-item', function() {
            $('.brand-circle, .brand-name').removeClass('active');
            $(this).find('.brand-circle, .brand-name').addClass('active');
            $('#brand').val($(this).find('.brand-name').text());
            calculateSavings();
        });

        $('#services-grid').on('click', '.service-item', function() {
            $(this).find('.service-circle, .service-name').toggleClass('active');
            updateSelectedServices();
            calculateSavings();
        });

        $('#promo-options').on('click', '.promo-option', function() {
            $('.promo-circle, .promo-value, .promo-description').removeClass('active');
            $(this).find('.promo-circle, .promo-value, .promo-description').addClass('active');
            $('#promo').val($(this).find('.promo-value').text().replace('%', ''));
            calculateSavings();
        });

        function updateSelectedServices() {
            const selectedServices = [];
            $('#services-grid .service-item').each(function() {
                if ($(this).find('.service-circle').hasClass('active')) {
                    selectedServices.push($(this).find('.service-name').text());
                }
            });
            $('#services').val(selectedServices.join(','));
        }

        function calculateSavings() {
            $.ajax({
                url: '{{ route("calculator.calculate") }}',
                method: 'POST',
                data: $('#calculatorForm').serialize(),
                success: function(data) {
                    $('#yearly-savings').text(data.yearly_savings);
                    $('#monthly-savings').text(data.monthly_savings);
                },
                error: function(xhr) {
                    console.error('Error calculating savings:', xhr.responseJSON.error);
                }
            });
        }

        // Volume slider functionality
        let isDragging = false;
        const slider = $('.slider');
        const thumb = $('.slider-thumb');
        const fill = $('.slider-fill');
        const volumeDisplay = $('#volume-display');
        const pumpingInput = $('#pumping');

        function updateSliderPosition(clientX) {
            const sliderRect = slider[0].getBoundingClientRect();
            let position = (clientX - sliderRect.left) / sliderRect.width;
            position = Math.max(0, Math.min(1, position));

            const value = Math.round(position * 500);
            volumeDisplay.text(value);
            pumpingInput.val(value);

            thumb.css('left', `${position * 100}%`);
            fill.css('width', `${position * 100}%`);

            calculateSavings();
        }

        thumb.on('mousedown', function(e) {
            isDragging = true;
            e.preventDefault();
        });

        $(document).on('mousemove', function(e) {
            if (isDragging) {
                updateSliderPosition(e.clientX);
            }
        });

        $(document).on('mouseup', function() {
            isDragging = false;
        });

        slider.on('click', function(e) {
            updateSliderPosition(e.clientX);
        });

        // Initial setup
        updateBrands();
        updateServices();
        updatePromo('Избранный');

        // Add popup functionality
        $(document).ready(function() {
            const innInput = $('#inn');
            const phoneInput = $('#phone');
            const emailInput = $('#email');
            const consentCheckbox = $('#consent-checkbox');
            const consentInput = $('#consent');
            const submitButton = $('#submit-btn');
            const closeButton = $('.close-button');
            const modalOverlay = $('#orderModal');

            const innError = $('#inn-error');
            const phoneError = $('#phone-error');
            const emailError = $('#email-error');
            const consentError = $('#consent-error');

            // Show modal when clicking the order button
            $('#submit-button').click(function() {
                modalOverlay.css('display', 'flex');
            });

            // Close modal when clicking close button or overlay
            closeButton.click(function() {
                modalOverlay.css('display', 'none');
            });

            modalOverlay.click(function(e) {
                if (e.target === this) {
                    modalOverlay.css('display', 'none');
                }
            });

            // Toggle checkbox
            consentCheckbox.click(function() {
                consentInput.prop('checked', !consentInput.prop('checked'));
                if (consentInput.prop('checked')) {
                    consentCheckbox.css('backgroundColor', '#00cfcc');
                    consentCheckbox.find('.checkmark').css('display', 'block');
                    consentError.css('display', 'none');
                } else {
                    consentCheckbox.css('backgroundColor', '#fafafa');
                    consentCheckbox.find('.checkmark').css('display', 'none');
                }
            });

            // Initialize checkbox state
            consentCheckbox.css('backgroundColor', '#fafafa');
            consentCheckbox.find('.checkmark').css('display', 'none');

            // Form validation
            function validateInn(inn) {
                return /^(\d{10}|\d{12})$/.test(inn);
            }

            function validatePhone(phone) {
                return /^\+?[78][-\(]?\d{3}\)?-?\d{3}-?\d{2}-?\d{2}$/.test(phone);
            }

            function validateEmail(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }

            // Input validation
            innInput.on('input', function() {
                if (!validateInn(this.value)) {
                    innError.css('display', 'block');
                } else {
                    innError.css('display', 'none');
                }
            });

            phoneInput.on('input', function() {
                if (!validatePhone(this.value)) {
                    phoneError.css('display', 'block');
                } else {
                    phoneError.css('display', 'none');
                }
            });

            emailInput.on('input', function() {
                if (!validateEmail(this.value)) {
                    emailError.css('display', 'block');
                } else {
                    emailError.css('display', 'none');
                }
            });

            // Form submission
            submitButton.click(function() {
                let isValid = true;

                if (!validateInn(innInput.val())) {
                    innError.css('display', 'block');
                    isValid = false;
                }

                if (!validatePhone(phoneInput.val())) {
                    phoneError.css('display', 'block');
                    isValid = false;
                }

                if (!validateEmail(emailInput.val())) {
                    emailError.css('display', 'block');
                    isValid = false;
                }

                if (!consentInput.prop('checked')) {
                    consentError.css('display', 'block');
                    isValid = false;
                }

                if (isValid) {
                    // Here you would typically send the form data to the server
                    alert('Форма успешно отправлена!');
                    modalOverlay.css('display', 'none');
                }
            });
        });
    </script>
</body>
</html>
