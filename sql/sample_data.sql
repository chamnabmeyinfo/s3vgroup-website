-- Sample Data for S3V Group Website
-- This file contains sample data for products, teams, testimonials, sliders, etc.
-- Import this AFTER schema.sql and site_options.sql

-- ============================================
-- SAMPLE PRODUCTS
-- ============================================
-- Note: Categories must exist first (from schema.sql)

INSERT INTO products (id, name, slug, sku, summary, description, specs, heroImage, price, status, highlights, categoryId, createdAt, updatedAt) VALUES
('prod_001', 'Electric Forklift 3.5 Ton', 'electric-forklift-35-ton', 'FL-ELEC-3500', 'High-performance electric forklift perfect for indoor warehouse operations. Zero emissions and quiet operation.', 'This electric forklift features advanced battery technology, ergonomic design, and excellent maneuverability. Ideal for warehouses, distribution centers, and manufacturing facilities.', '{"capacity": "3.5 tons", "lift_height": "6 meters", "power": "48V battery", "weight": "4500 kg", "dimensions": "2.3m x 1.2m x 2.1m"}', 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800&q=80', 45000.00, 'PUBLISHED', '["Zero emissions", "Quiet operation", "Low maintenance", "Ergonomic design"]', 'cat_001', NOW(), NOW()),

('prod_002', 'Diesel Forklift 5 Ton', 'diesel-forklift-5-ton', 'FL-DSL-5000', 'Heavy-duty diesel forklift for outdoor and rugged applications. Powerful engine and excellent lifting capacity.', 'Built for tough conditions, this diesel forklift delivers reliable performance in outdoor yards, construction sites, and heavy industrial environments.', '{"capacity": "5 tons", "lift_height": "7 meters", "engine": "4.5L diesel", "weight": "6800 kg", "dimensions": "2.8m x 1.4m x 2.3m"}', 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=800&q=80', 65000.00, 'PUBLISHED', '["Heavy duty", "Outdoor capable", "High capacity", "Durable construction"]', 'cat_001', NOW(), NOW()),

('prod_003', 'Pallet Jack Electric', 'pallet-jack-electric', 'PJ-ELEC-2500', 'Compact electric pallet jack for efficient material handling. Easy to operate and maintain.', 'Perfect for moving pallets in tight spaces. Features ergonomic controls and long-lasting battery.', '{"capacity": "2.5 tons", "lift_height": "20 cm", "power": "24V battery", "weight": "280 kg", "width": "68 cm"}', 'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?w=800&q=80', 3500.00, 'PUBLISHED', '["Compact design", "Easy operation", "Battery powered", "Affordable"]', 'cat_002', NOW(), NOW()),

('prod_004', 'Heavy Duty Pallet Racking', 'heavy-duty-pallet-racking', 'PR-HD-1000', 'Industrial-grade pallet racking system. Adjustable beam heights and high load capacity.', 'Maximize your warehouse storage with this robust racking system. Easy to install and configure for your specific needs.', '{"capacity": "1000 kg per level", "height": "Up to 10 meters", "width": "2.7m", "depth": "1.0m", "material": "Steel"}', 'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?w=800&q=80', 850.00, 'PUBLISHED', '["High capacity", "Adjustable", "Durable", "Easy installation"]', 'cat_003', NOW(), NOW()),

('prod_005', 'Mobile Conveyor Belt', 'mobile-conveyor-belt', 'CV-MOB-600', 'Portable conveyor system for loading and unloading operations. Adjustable height and angle.', 'Increase efficiency with this mobile conveyor. Perfect for trucks, warehouses, and distribution centers.', '{"length": "6 meters", "width": "60 cm", "speed": "Variable", "power": "Electric", "weight": "450 kg"}', 'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?w=800&q=80', 12000.00, 'PUBLISHED', '["Portable", "Adjustable", "Efficient", "Versatile"]', 'cat_004', NOW(), NOW()),

('prod_006', 'Digital Weighing Scale 10 Ton', 'digital-weighing-scale-10-ton', 'WS-DIG-10000', 'Precision digital scale for heavy-duty weighing applications. Large display and durable construction.', 'Accurate weighing for industrial applications. Features large LED display and multiple weighing units.', '{"capacity": "10 tons", "accuracy": "±0.1%", "display": "LED", "platform": "1.2m x 1.5m", "power": "AC/DC"}', 'https://images.unsplash.com/photo-1628177142898-93e36e4e3a50?w=800&q=80', 2800.00, 'PUBLISHED', '["High accuracy", "Large display", "Durable", "Multiple units"]', 'cat_001', NOW(), NOW())

ON DUPLICATE KEY UPDATE name=name;

-- ============================================
-- SAMPLE TEAM MEMBERS
-- ============================================

INSERT INTO team_members (id, name, title, bio, photo, email, phone, linkedin, priority, status, createdAt, updatedAt) VALUES
('team_001', 'Sok Pisey', 'General Manager', 'With over 15 years of experience in industrial equipment and warehouse solutions, Sok leads our team with expertise and dedication.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&q=80', 'sok.pisey@s3vtgroup.com.kh', '+855 12 345 678', 'https://linkedin.com/in/sokpisey', 100, 'ACTIVE', NOW(), NOW()),

('team_002', 'Chan Sophal', 'Sales Director', 'Chan brings extensive knowledge of material handling equipment and helps clients find the perfect solutions for their needs.', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&q=80', 'chan.sophal@s3vtgroup.com.kh', '+855 12 345 679', 'https://linkedin.com/in/chansophal', 90, 'ACTIVE', NOW(), NOW()),

('team_003', 'Lim Srey Pich', 'Technical Support Manager', 'Lim ensures all equipment is properly installed and maintained. Expert in forklift maintenance and repair.', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&q=80', 'lim.srey@s3vtgroup.com.kh', '+855 12 345 680', 'https://linkedin.com/in/limsrey', 80, 'ACTIVE', NOW(), NOW()),

('team_004', 'Meas Ratha', 'Operations Manager', 'Meas coordinates logistics and ensures smooth operations across all departments. Expert in warehouse optimization.', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&q=80', 'meas.ratha@s3vtgroup.com.kh', '+855 12 345 681', 'https://linkedin.com/in/measratha', 70, 'ACTIVE', NOW(), NOW())

ON DUPLICATE KEY UPDATE name=name;

-- ============================================
-- SAMPLE TESTIMONIALS
-- ============================================

INSERT INTO testimonials (id, name, company, position, rating, testimonial, photo, featured, priority, status, createdAt, updatedAt) VALUES
('test_001', 'Sok Pisey', 'ABC Logistics Co., Ltd.', 'Operations Manager', 5, 'S3V Group provided excellent forklift solutions for our warehouse. The equipment is reliable and their service is outstanding. Highly recommended!', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80', 1, 100, 'PUBLISHED', NOW(), NOW()),

('test_002', 'Chan Sophal', 'Cambodia Manufacturing Inc.', 'Factory Manager', 5, 'We purchased material handling equipment from S3V Group and couldn\'t be happier. Professional service and quality products. Our operations have improved significantly.', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=200&q=80', 1, 90, 'PUBLISHED', NOW(), NOW()),

('test_003', 'Lim Srey Pich', 'Royal Distribution Center', 'Warehouse Director', 5, 'The storage racking system we got from S3V Group has maximized our warehouse space. Installation was smooth and the team was very professional.', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=200&q=80', 1, 80, 'PUBLISHED', NOW(), NOW()),

('test_004', 'Meas Ratha', 'Phnom Penh Trading Co.', 'Supply Chain Manager', 5, 'Excellent customer service and high-quality equipment. S3V Group understands our needs and delivers solutions that work. Great partnership!', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&q=80', 1, 70, 'PUBLISHED', NOW(), NOW()),

('test_005', 'Heng Sokunthea', 'Modern Factory Solutions', 'CEO', 5, 'We\'ve been working with S3V Group for years. Their expertise in industrial equipment is unmatched. Always reliable and professional.', 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=200&q=80', 1, 60, 'PUBLISHED', NOW(), NOW()),

('test_006', 'Kong Vannak', 'Cambodia Logistics Hub', 'Operations Director', 5, 'The electric forklifts we purchased have reduced our operating costs significantly. S3V Group provided excellent training and ongoing support.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80', 1, 50, 'PUBLISHED', NOW(), NOW())

ON DUPLICATE KEY UPDATE name=name;

-- ============================================
-- SAMPLE HERO SLIDER SLIDES
-- ============================================

INSERT INTO sliders (id, title, subtitle, description, image_url, link_url, link_text, button_color, priority, status, createdAt, updatedAt) VALUES
('slider_001', 'Warehouse & Factory Equipment Solutions', 'Leading Supplier in Cambodia', 'Complete range of industrial equipment including forklifts, material handling systems, storage solutions, and warehouse automation.', 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=1920&q=80', '/products.php', 'Explore Products', '#0b3a63', 100, 'PUBLISHED', NOW(), NOW()),

('slider_002', 'Forklift Solutions', 'Premium Quality Equipment', 'Electric, diesel, and gas forklifts from trusted manufacturers. Perfect for warehouses, factories, and distribution centers.', 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=1920&q=80', '/products.php?category=forklifts', 'View Forklifts', '#fa4f26', 90, 'PUBLISHED', NOW(), NOW()),

('slider_003', 'Material Handling Systems', 'Efficient & Reliable', 'Conveyors, pallet jacks, and automated systems to streamline your operations and increase productivity.', 'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?w=1920&q=80', '/products.php?category=material-handling', 'Learn More', '#1a5a8a', 80, 'PUBLISHED', NOW(), NOW()),

('slider_004', 'Professional Installation & Service', 'Expert Support', 'Our experienced team provides installation, maintenance, and repair services to keep your equipment running smoothly.', 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=1920&q=80', '/quote.php', 'Request Service', '#0b3a63', 70, 'PUBLISHED', NOW(), NOW()),

('slider_005', 'Industrial Storage Solutions', 'Maximize Your Space', 'Pallet racking, shelving systems, and warehouse storage solutions designed to optimize your storage capacity.', 'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?w=1920&q=80', '/products.php?category=storage-solutions', 'View Storage Solutions', '#fa4f26', 60, 'PUBLISHED', NOW(), NOW())

ON DUPLICATE KEY UPDATE title=title;

-- ============================================
-- SAMPLE QUOTE REQUESTS
-- ============================================

INSERT INTO quote_requests (id, companyName, contactName, email, phone, message, items, status, source, createdAt, updatedAt) VALUES
('quote_001', 'ABC Manufacturing Co.', 'John Doe', 'john@abcmfg.com', '+855 12 345 678', 'We need 3 electric forklifts for our new warehouse facility. Please provide a quote with delivery timeline.', '{"items": [{"product": "Electric Forklift 3.5 Ton", "quantity": 3}]}', 'NEW', 'website', NOW(), NOW()),

('quote_002', 'XYZ Logistics Ltd.', 'Jane Smith', 'jane@xyzltd.com', '+855 12 345 679', 'Interested in pallet racking system for 5000 sqm warehouse. Need consultation and quote.', '{"items": [{"product": "Heavy Duty Pallet Racking", "quantity": 50}]}', 'IN_PROGRESS', 'website', NOW(), NOW())

ON DUPLICATE KEY UPDATE companyName=companyName;

-- ============================================
-- SAMPLE COMPANY STORY
-- ============================================

INSERT INTO company_story (id, title, content, mission, vision, values, achievements, status, createdAt, updatedAt) VALUES
('story_001', 'Our Story', 'S3V Group was founded with a vision to provide world-class industrial equipment solutions to businesses across Cambodia. Since our establishment, we have been committed to excellence, innovation, and customer satisfaction.', 'To be Cambodia\'s leading provider of industrial equipment and warehouse solutions, helping businesses optimize their operations and achieve their goals.', 'To transform the industrial landscape in Cambodia through innovative solutions, exceptional service, and lasting partnerships.', 'Integrity, Excellence, Innovation, Customer Focus, Teamwork', 'Over 500 satisfied customers, 1000+ equipment installations, 15+ years of experience, ISO certified operations', 'PUBLISHED', NOW(), NOW())

ON DUPLICATE KEY UPDATE title=title;

-- ============================================
-- SAMPLE CEO MESSAGE
-- ============================================

INSERT INTO ceo_message (id, title, message, photo, name, position, signature, displayOrder, status, createdAt, updatedAt) VALUES
('ceo_001', 'Message from CEO', 'Welcome to S3V Group. We are dedicated to providing the highest quality industrial equipment and exceptional service to our customers. Our team of experts is here to help you find the perfect solutions for your business needs. Thank you for choosing S3V Group.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&q=80', 'Sok Pisey', 'Chief Executive Officer', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80', 0, 'PUBLISHED', NOW(), NOW())

ON DUPLICATE KEY UPDATE title=title;

