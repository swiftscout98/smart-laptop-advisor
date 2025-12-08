-- Add 30 Laptops (2023-2025)
INSERT INTO products (product_name, brand, price, cpu, gpu, ram_gb, storage_gb, display_size, battery_life, product_category, primary_use_case, description, image_url, is_active) VALUES
-- Gaming Laptops (Gamer)
('ASUS ROG Strix Scar 18 (2024)', 'ASUS', 3899.00, 'Intel Core i9-14900HX', 'NVIDIA GeForce RTX 4090', 32, 2000, 18.0, '5 hours', 'laptop', 'Gamer', 'Ultimate gaming powerhouse with 18-inch Mini LED display.', 'assets/images/products/rog_scar_18.jpg', 1),
('Razer Blade 16 (2024)', 'Razer', 3299.99, 'Intel Core i9-14900HX', 'NVIDIA GeForce RTX 4080', 32, 1000, 16.0, '5 hours', 'laptop', 'Gamer', 'Sleek and powerful with the world''s first OLED 240Hz display.', 'assets/images/products/razer_blade_16.jpg', 1),
('MSI Titan 18 HX', 'MSI', 4999.00, 'Intel Core i9-14900HX', 'NVIDIA GeForce RTX 4090', 128, 4000, 18.0, '4 hours', 'laptop', 'Gamer', 'Desktop replacement with mechanical keyboard and extreme performance.', 'assets/images/products/msi_titan_18.jpg', 1),
('Alienware m18 R2', 'Dell', 2599.00, 'Intel Core i9-14900HX', 'NVIDIA GeForce RTX 4070', 16, 1000, 18.0, '5 hours', 'laptop', 'Gamer', 'Massive screen real estate for immersive gaming.', 'assets/images/products/alienware_m18.jpg', 1),
('Lenovo Legion 9i Gen 9', 'Lenovo', 4399.00, 'Intel Core i9-14900HX', 'NVIDIA GeForce RTX 4090', 64, 2000, 16.0, '5 hours', 'laptop', 'Gamer', 'Liquid-cooled gaming laptop with AI-tuned performance.', 'assets/images/products/legion_9i.jpg', 1),
('Acer Predator Helios 18', 'Acer', 2499.00, 'Intel Core i9-14900HX', 'NVIDIA GeForce RTX 4080', 32, 2000, 18.0, '4 hours', 'laptop', 'Gamer', 'High-performance gaming laptop with vibrant Mini-LED screen.', 'assets/images/products/predator_helios_18.jpg', 1),
('HP Omen Transcend 14', 'HP', 1699.00, 'Intel Core Ultra 9 185H', 'NVIDIA GeForce RTX 4060', 16, 1000, 14.0, '8 hours', 'laptop', 'Gamer', 'Lightweight portable gaming laptop with OLED display.', 'assets/images/products/omen_transcend_14.jpg', 1),
('ASUS Zephyrus G14 (2024)', 'ASUS', 1999.00, 'AMD Ryzen 9 8945HS', 'NVIDIA GeForce RTX 4070', 32, 1000, 14.0, '9 hours', 'laptop', 'Gamer', 'The king of compact gaming laptops redesign with OLED.', 'assets/images/products/zephyrus_g14.jpg', 1),
('GIGABYTE AORUS 17X', 'GIGABYTE', 2299.00, 'Intel Core i9-14900HX', 'NVIDIA GeForce RTX 4080', 32, 2000, 17.3, '5 hours', 'laptop', 'Gamer', 'Powerful craftsmanship for hardcore gamers.', 'assets/images/products/aorus_17x.jpg', 1),
('Dell G16 Gaming Laptop', 'Dell', 1499.00, 'Intel Core i7-13650HX', 'NVIDIA GeForce RTX 4060', 16, 1000, 16.0, '5 hours', 'laptop', 'Gamer', 'Best value mainstream gaming laptop.', 'assets/images/products/dell_g16.jpg', 1),

-- Professional / Business Laptops (Professional)
('Apple MacBook Pro 14 (M3 Max)', 'Apple', 3199.00, 'Apple M3 Max', 'Integrated 30-core GPU', 36, 1000, 14.2, '18 hours', 'laptop', 'Professional', 'Unmatched performance and battery life for professionals.', 'assets/images/products/macbook_pro_14_m3.jpg', 1),
('Dell XPS 16 (2024)', 'Dell', 2899.00, 'Intel Core Ultra 7 155H', 'NVIDIA GeForce RTX 4060', 32, 1000, 16.3, '12 hours', 'laptop', 'Professional', 'Futuristic design with invisible trackpad and OLED touch.', 'assets/images/products/dell_xps_16.jpg', 1),
('Lenovo ThinkPad X1 Carbon Gen 12', 'Lenovo', 1899.00, 'Intel Core Ultra 7 155U', 'Integrated Intel Graphics', 32, 512, 14.0, '14 hours', 'laptop', 'Professional', 'The ultimate business ultrabook, now with AI capabilities.', 'assets/images/products/thinkpad_x1_gen12.jpg', 1),
('HP Dragonfly G4', 'HP', 1749.00, 'Intel Core i7-1365U', 'Integrated Intel Iris Xe', 16, 1000, 13.5, '13 hours', 'laptop', 'Professional', 'Incredibly light business laptop with great webcam features.', 'assets/images/products/hp_dragonfly_g4.jpg', 1),
('Microsoft Surface Laptop 6', 'Microsoft', 1599.00, 'Intel Core Ultra 5 135H', 'Integrated Intel Graphics', 16, 512, 13.5, '15 hours', 'laptop', 'Professional', 'Sleek, productive, and AI-powered.', 'assets/images/products/surface_laptop_6.jpg', 1),
('Framework Laptop 13 (AMD)', 'Framework', 1499.00, 'AMD Ryzen 7 7840U', 'Integrated Radeon 780M', 32, 1000, 13.5, '10 hours', 'laptop', 'Professional', 'Modular, repairable, and upgradeable.', 'assets/images/products/framework_13.jpg', 1),
('LG Gram Pro 16 2-in-1', 'LG', 1799.00, 'Intel Core Ultra 7 155H', 'Integrated Intel Graphics', 16, 1000, 16.0, '16 hours', 'laptop', 'Professional', 'World''s lightest 16-inch 2-in-1 laptop.', 'assets/images/products/lg_gram_pro_16.jpg', 1),
('Samsung Galaxy Book4 Ultra', 'Samsung', 2399.00, 'Intel Core Ultra 9 185H', 'NVIDIA GeForce RTX 4070', 32, 1000, 16.0, '12 hours', 'laptop', 'Professional', 'Premium AMOLED display and seamless Galaxy ecosystem.', 'assets/images/products/galaxy_book4_ultra.jpg', 1),

-- Creative Laptops (Creative)
('ASUS ProArt Studiobook 16', 'ASUS', 2599.00, 'Intel Core i9-13980HX', 'NVIDIA RTX 3000 Ada', 64, 2000, 16.0, '6 hours', 'laptop', 'Creative', 'Designed for creators with a physical dial for precision control.', 'assets/images/products/proart_studiobook.jpg', 1),
('MacBook Air 15 (M3)', 'Apple', 1499.00, 'Apple M3', 'Integrated 10-core GPU', 16, 512, 15.3, '18 hours', 'laptop', 'Creative', 'Big screen, thin design, perfect for on-the-go creators.', 'assets/images/products/macbook_air_15_m3.jpg', 1),
('MSI Creator Z17 HX Studio', 'MSI', 2899.00, 'Intel Core i9-13950HX', 'NVIDIA GeForce RTX 4070', 32, 2000, 17.0, '6 hours', 'laptop', 'Creative', 'Combines aesthetics with top-tier performance for designers.', 'assets/images/products/creator_z17.jpg', 1),
('Dell Precision 5680', 'Dell', 3200.00, 'Intel Core i7-13800H', 'NVIDIA RTX 2000 Ada', 32, 1000, 16.0, '10 hours', 'laptop', 'Creative', 'Mobile workstation power in a thin chassis.', 'assets/images/products/precision_5680.jpg', 1),
('Surface Laptop Studio 2', 'Microsoft', 2699.00, 'Intel Core i7-13700H', 'NVIDIA GeForce RTX 4060', 64, 1000, 14.4, '12 hours', 'laptop', 'Creative', 'Pull-forward touchscreen for drawing and designing.', 'assets/images/products/surface_studio_2.jpg', 1),
('Lenovo Yoga Pro 9i', 'Lenovo', 1899.00, 'Intel Core Ultra 9 185H', 'NVIDIA GeForce RTX 4060', 32, 1000, 16.0, '8 hours', 'laptop', 'Creative', 'Mini-LED display perfect for photo editing.', 'assets/images/products/yoga_pro_9i.jpg', 1),

-- Student / Home User (Student, Home User)
('Acer Swift Go 14', 'Acer', 849.00, 'Intel Core Ultra 5 125H', 'Integrated Intel Graphics', 16, 512, 14.0, '10 hours', 'laptop', 'Student', 'OLED display on a budget, perfect for students.', 'assets/images/products/swift_go_14.jpg', 1),
('ASUS Vivobook S 15 OLED', 'ASUS', 999.00, 'Intel Core Ultra 7 155H', 'Integrated Intel Graphics', 16, 1000, 15.6, '11 hours', 'laptop', 'Student', 'Vibrant screen and solid performance for everyday classwork.', 'assets/images/products/vivobook_s15.jpg', 1),
('HP Envy x360 14', 'HP', 959.00, 'AMD Ryzen 7 8840HS', 'Integrated Radeon 780M', 16, 1000, 14.0, '12 hours', 'laptop', 'Student', 'Versatile 2-in-1 for note-taking and studying.', 'assets/images/products/envy_x360_14.jpg', 1),
('Lenovo IdeaPad Slim 5 Gen 9', 'Lenovo', 749.00, 'AMD Ryzen 5 8645HS', 'Integrated Radeon Graphics', 16, 512, 16.0, '12 hours', 'laptop', 'Home User', 'Reliable and affordable big-screen laptop for home use.', 'assets/images/products/ideapad_slim_5.jpg', 1),
('Dell Inspiron 14 Plus', 'Dell', 999.00, 'Intel Core i7-13620H', 'Integrated Intel UHD', 16, 1000, 14.0, '10 hours', 'laptop', 'Home User', 'Solid build quality and reliable performance.', 'assets/images/products/inspiron_14_plus.jpg', 1),
('MacBook Air 13 (M2)', 'Apple', 999.00, 'Apple M2', 'Integrated 8-core GPU', 8, 256, 13.6, '18 hours', 'laptop', 'Student', 'The gold standard for student laptops.', 'assets/images/products/macbook_air_m2.jpg', 1),

-- Accessories
-- Mice
('Logitech MX Master 3S', 'Logitech', 99.99, NULL, NULL, NULL, NULL, NULL, '70 days', 'mouse', 'Professional', 'Ultimate productivity mouse with quiet clicks.', 'assets/images/products/mx_master_3s.jpg', 1),
('Razer DeathAdder V3 Pro', 'Razer', 149.99, NULL, NULL, NULL, NULL, NULL, '90 hours', 'mouse', 'Gamer', 'Ultra-lightweight esports gaming mouse.', 'assets/images/products/deathadder_v3.jpg', 1),
('Logitech G502 X Plus', 'Logitech', 139.99, NULL, NULL, NULL, NULL, NULL, '120 hours', 'mouse', 'Gamer', 'Legendary gaming mouse reinvented with hybrid switches.', 'assets/images/products/g502_x.jpg', 1),
('Apple Magic Mouse', 'Apple', 79.00, NULL, NULL, NULL, NULL, NULL, '30 days', 'mouse', 'Creative', 'Multi-touch surface mouse for Mac users.', 'assets/images/products/magic_mouse.jpg', 1),
('Keychron M3 Wireless', 'Keychron', 49.00, NULL, NULL, NULL, NULL, NULL, '70 hours', 'mouse', 'Home User', 'Best value wireless optical mouse.', 'assets/images/products/keychron_m3.jpg', 1),

-- Keyboards
('Keychron Q1 Pro', 'Keychron', 199.00, NULL, NULL, NULL, NULL, NULL, '100 hours', 'keyboard', 'Professional', 'Custom mechanical keyboard with aluminum body.', 'assets/images/products/keychron_q1_pro.jpg', 1),
('Logitech MX Keys S', 'Logitech', 109.99, NULL, NULL, NULL, NULL, NULL, '10 days', 'keyboard', 'Professional', 'Low-profile wireless keyboard for coding and writing.', 'assets/images/products/mx_keys_s.jpg', 1),
('Razer Huntsman V3 Pro', 'Razer', 249.99, NULL, NULL, NULL, NULL, NULL, NULL, 'keyboard', 'Gamer', 'Analog optical switches for rapid triggers.', 'assets/images/products/huntsman_v3.jpg', 1),
('NuPhy Air75 V2', 'NuPhy', 119.99, NULL, NULL, NULL, NULL, NULL, '220 hours', 'keyboard', 'Creative', 'Slim mechanical keyboard perfect for travel.', 'assets/images/products/nuphy_air75.jpg', 1),
('Wooting 60HE', 'Wooting', 174.99, NULL, NULL, NULL, NULL, NULL, NULL, 'keyboard', 'Gamer', 'The fastest keyboard for competitive gaming.', 'assets/images/products/wooting_60he.jpg', 1),

-- Headsets
('Sony WH-1000XM5', 'Sony', 399.99, NULL, NULL, NULL, NULL, NULL, '30 hours', 'headset', 'Home User', 'Industry-leading noise canceling headphones.', 'assets/images/products/sony_xm5.jpg', 1),
('Bose QuietComfort Ultra', 'Bose', 429.00, NULL, NULL, NULL, NULL, NULL, '24 hours', 'headset', 'Professional', 'World-class comfort and silence.', 'assets/images/products/bose_qc_ultra.jpg', 1),
('SteelSeries Arctis Nova Pro Wireless', 'SteelSeries', 349.99, NULL, NULL, NULL, NULL, NULL, 'Infinity', 'headset', 'Gamer', 'Premium gaming audio with dual-battery system.', 'assets/images/products/arctis_nova_pro.jpg', 1),
('Razer BlackShark V2 Pro (2023)', 'Razer', 199.99, NULL, NULL, NULL, NULL, NULL, '70 hours', 'headset', 'Gamer', 'Esports headset with crystal-clear microphone.', 'assets/images/products/blackshark_v2_pro.jpg', 1),
('HyperX Cloud III Wireless', 'HyperX', 169.99, NULL, NULL, NULL, NULL, NULL, '120 hours', 'headset', 'Home User', 'Comfortable and durable headset with massive battery.', 'assets/images/products/cloud_iii.jpg', 1),

-- Monitors
('Dell UltraSharp U2723QE', 'Dell', 629.99, NULL, NULL, NULL, NULL, 27.0, NULL, 'monitor', 'Professional', '4K IPS Black monitor with incredible contrast.', 'assets/images/products/dell_u2723qe.jpg', 1),
('LG C3 42-inch OLED', 'LG', 999.99, NULL, NULL, NULL, NULL, 42.0, NULL, 'monitor', 'Gamer', 'OLED TV widely used as a high-end gaming monitor.', 'assets/images/products/lg_c3_42.jpg', 1),
('Samsung Odyssey OLED G9', 'Samsung', 1799.99, NULL, NULL, NULL, NULL, 49.0, NULL, 'monitor', 'Gamer', 'Massive ultrawide OLED for immersive simulation.', 'assets/images/products/odyssey_g9_oled.jpg', 1),
('ASUS ProArt Display PA279CRV', 'ASUS', 499.00, NULL, NULL, NULL, NULL, 27.0, NULL, 'monitor', 'Creative', 'Color-accurate 4K monitor for designers.', 'assets/images/products/proart_pa279.jpg', 1),
('BenQ ScreenBar Halo', 'BenQ', 179.00, NULL, NULL, NULL, NULL, NULL, NULL, 'other', 'Professional', 'Advanced monitor light bar for eye comfort.', 'assets/images/products/screenbar_halo.jpg', 1);
