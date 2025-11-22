<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

use App\Database\Connection;
use App\Domain\Content\TeamMemberRepository;

$db = getDB();
$repository = new TeamMemberRepository($db);

echo "👥 Seeding team members with pictures...\n\n";

// Team members with profile pictures
$teamMembers = [
    [
        'name' => 'Sok Chen',
        'title' => 'Chief Executive Officer',
        'bio' => 'Founder and CEO with over 15 years of experience in warehouse and factory equipment solutions. Leading S3V Group to become Cambodia\'s premier supplier of industrial equipment.',
        'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=400&auto=format&fit=crop&q=85',
        'email' => 'sok.chen@s3vgroup.com',
        'phone' => '+855 12 345 678',
        'linkedin' => 'https://linkedin.com/in/sokchen',
        'priority' => 100,
        'status' => 'ACTIVE',
    ],
    [
        'name' => 'Lim Srey Pich',
        'title' => 'Operations Manager',
        'bio' => 'Expert in warehouse operations and material handling systems. Ensures smooth operations and excellent customer service for all our clients.',
        'photo' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&h=400&auto=format&fit=crop&q=85',
        'email' => 'srey.pich@s3vgroup.com',
        'phone' => '+855 12 345 679',
        'linkedin' => 'https://linkedin.com/in/sreypich',
        'priority' => 90,
        'status' => 'ACTIVE',
    ],
    [
        'name' => 'Heng Vannak',
        'title' => 'Sales Director',
        'bio' => 'Experienced sales professional specializing in forklifts and material handling equipment. Helping businesses find the right solutions for their needs.',
        'photo' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&h=400&auto=format&fit=crop&q=85',
        'email' => 'vannak.heng@s3vgroup.com',
        'phone' => '+855 12 345 680',
        'linkedin' => 'https://linkedin.com/in/vannakheng',
        'priority' => 80,
        'status' => 'ACTIVE',
    ],
    [
        'name' => 'Chan Sophal',
        'title' => 'Technical Support Manager',
        'bio' => 'Technical expert in forklift maintenance and repair. Leads our service team to ensure all equipment operates at peak performance.',
        'photo' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&h=400&auto=format&fit=crop&q=85',
        'email' => 'sophal.chan@s3vgroup.com',
        'phone' => '+855 12 345 681',
        'linkedin' => 'https://linkedin.com/in/sophalchan',
        'priority' => 70,
        'status' => 'ACTIVE',
    ],
    [
        'name' => 'Meas Ratha',
        'title' => 'Warehouse Solutions Specialist',
        'bio' => 'Specializes in storage and racking solutions. Designs custom warehouse layouts to maximize efficiency and space utilization.',
        'photo' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=400&h=400&auto=format&fit=crop&q=85',
        'email' => 'ratha.meas@s3vgroup.com',
        'phone' => '+855 12 345 682',
        'linkedin' => 'https://linkedin.com/in/rathameas',
        'priority' => 60,
        'status' => 'ACTIVE',
    ],
    [
        'name' => 'Kong Sothea',
        'title' => 'Customer Relations Manager',
        'bio' => 'Dedicated to building strong relationships with clients. Ensures customer satisfaction and provides ongoing support throughout the equipment lifecycle.',
        'photo' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=400&h=400&auto=format&fit=crop&q=85',
        'email' => 'sothea.kong@s3vgroup.com',
        'phone' => '+855 12 345 683',
        'linkedin' => 'https://linkedin.com/in/sotheakong',
        'priority' => 50,
        'status' => 'ACTIVE',
    ],
];

echo "👥 Creating team members...\n";
$created = 0;
$skipped = 0;

foreach ($teamMembers as $member) {
    // Check if member already exists by name
    $stmt = $db->prepare("SELECT id FROM team_members WHERE name = :name LIMIT 1");
    $stmt->execute([':name' => $member['name']]);
    if ($stmt->fetch()) {
        echo "  ⏭️  Skipped: {$member['name']} (already exists)\n";
        $skipped++;
        continue;
    }
    
    try {
        $repository->create($member);
        echo "  ✅ Created: {$member['name']} - {$member['title']}\n";
        $created++;
    } catch (Exception $e) {
        echo "  ⚠️  Error creating {$member['name']}: " . $e->getMessage() . "\n";
        $skipped++;
    }
}

echo "\n✨ Team member seeding completed!\n";
echo "   ✅ Created: {$created} team members\n";
echo "   ⏭️  Skipped: {$skipped} team members\n";
echo "\n💡 View team at:\n";
echo "   - Admin Panel: http://localhost:8080/admin/team.php\n";
echo "   - All team members have profile pictures from reliable sources\n";

