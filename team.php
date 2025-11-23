<?php
// Load bootstrap FIRST to ensure env() function is available
require_once __DIR__ . '/bootstrap/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/site.php';
require_once __DIR__ . '/includes/functions.php';

use App\Database\Connection;
use App\Domain\Content\TeamMemberRepository;

$db = getDB();
$repository = new TeamMemberRepository($db);
$team = $repository->active();

$pageTitle = 'Our Team';
$pageDescription = 'Meet our experienced team of warehouse and factory equipment specialists';
$primaryColor = option('primary_color', '#0b3a63');
$secondaryColor = option('secondary_color', '#1a5a8a');
$accentColor = option('accent_color', '#fa4f26');

include __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
    <!-- Header -->
    <div class="mb-12 text-center fade-in-up">
        <h1 class="text-4xl sm:text-5xl font-bold mb-4" style="color: <?php echo e($primaryColor); ?>;">
            Our Team
        </h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            Meet our experienced team of warehouse and factory equipment specialists
        </p>
    </div>

    <!-- Team Grid -->
    <?php if (!empty($team)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 sm:gap-10">
            <?php foreach ($team as $index => $member): ?>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden animate-on-scroll hover:scale-105 transform text-center" style="animation-delay: <?php echo ($index * 0.1); ?>s;">
                    <!-- Photo -->
                    <div class="pt-8 pb-4">
                        <?php if ($member['photo']): ?>
                            <img 
                                src="<?php echo e($member['photo']); ?>" 
                                alt="<?php echo e($member['name']); ?>" 
                                class="h-32 w-32 rounded-full object-cover mx-auto border-4 shadow-lg hover:scale-110 transition-transform duration-300"
                                style="border-color: <?php echo e($primaryColor); ?>;"
                                loading="lazy"
                            >
                        <?php else: ?>
                            <div class="h-32 w-32 rounded-full mx-auto flex items-center justify-center text-4xl font-bold text-white shadow-lg" style="background-color: <?php echo e($primaryColor); ?>;">
                                <?php echo strtoupper(substr($member['name'], 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Info -->
                    <div class="px-6 pb-8">
                        <h3 class="text-xl font-bold mb-1" style="color: <?php echo e($primaryColor); ?>;">
                            <?php echo e($member['name']); ?>
                        </h3>
                        <p class="text-sm font-medium text-gray-600 mb-1">
                            <?php echo e($member['title']); ?>
                        </p>
                        
                        <?php if ($member['department']): ?>
                            <p class="text-xs text-gray-500 mb-2"><?php echo e($member['department']); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($member['location']): ?>
                            <p class="text-xs text-gray-500 mb-3">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <?php echo e($member['location']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($member['bio']): ?>
                            <p class="text-gray-600 text-sm mb-3 line-clamp-3">
                                <?php echo nl2br(e($member['bio'])); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($member['expertise']): ?>
                            <div class="mb-3">
                                <p class="text-xs font-semibold text-gray-700 mb-1">Expertise:</p>
                                <p class="text-xs text-gray-600"><?php echo e($member['expertise']); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($member['languages']): ?>
                            <div class="mb-3">
                                <p class="text-xs font-semibold text-gray-700 mb-1">Languages:</p>
                                <p class="text-xs text-gray-600"><?php echo e($member['languages']); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Contact Info & Social Links -->
                        <div class="flex flex-wrap items-center justify-center gap-3 pt-4 border-t border-gray-200">
                            <?php if ($member['email']): ?>
                                <a href="mailto:<?php echo e($member['email']); ?>" class="text-gray-500 hover:text-[#0b3a63] transition-colors" title="Email">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <?php if ($member['phone']): ?>
                                <a href="tel:<?php echo e(str_replace(' ', '', $member['phone'])); ?>" class="text-gray-500 hover:text-[#0b3a63] transition-colors" title="Phone">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <?php if ($member['linkedin']): ?>
                                <a href="<?php echo e($member['linkedin']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-[#0077b5] transition-colors" title="LinkedIn">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($member['twitter']): ?>
                                <a href="<?php echo e($member['twitter']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-[#1DA1F2] transition-colors" title="Twitter/X">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($member['facebook']): ?>
                                <a href="<?php echo e($member['facebook']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-[#1877F2] transition-colors" title="Facebook">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($member['instagram']): ?>
                                <a href="<?php echo e($member['instagram']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-[#E4405F] transition-colors" title="Instagram">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($member['website']): ?>
                                <a href="<?php echo e($member['website']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-[#0b3a63] transition-colors" title="Website">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($member['github']): ?>
                                <a href="<?php echo e($member['github']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-[#333] transition-colors" title="GitHub">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($member['youtube']): ?>
                                <a href="<?php echo e($member['youtube']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-[#FF0000] transition-colors" title="YouTube">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($member['whatsapp']): ?>
                                <a href="https://wa.me/<?php echo e(preg_replace('/[^0-9]/', '', $member['whatsapp'])); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-[#25D366] transition-colors" title="WhatsApp">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($member['telegram']): ?>
                                <a href="https://t.me/<?php echo e(str_replace('@', '', $member['telegram'])); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-[#0088cc] transition-colors" title="Telegram">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.169 1.858-.896 6.728-.896 6.728-.896 1.858-1.236 1.858-1.683 1.858-.344 0-1.054-.447-1.054-.447s-2.216-1.4-4.342-2.17c-1.683-.721-2.17-1.4.344-2.458l7.368-3.682c1.054-.447 2.062-.896.896-1.4-.721-.344-6.297 1.858-8.423 2.313-1.492.344-1.829.722-1.829 1.172 0 .447.447.896 1.054 1.4 1.054.721 1.4 1.4 1.4 2.458-.169.447-.169 1.4-1.054 1.858-.721.447-2.216.896-2.216.896s-.169.344 0 .721c.169.344.447.447.721.447.896.344 1.829 1.4 2.216 1.858.447.344.896 1.054 1.683 1.054.447 0 1.054-.344 1.4-.721l1.829-6.041c.169-.344.447-.344.721-.169.169.169.169.344.169.721z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-16 sm:py-20 fade-in-up">
            <svg class="w-24 h-24 mx-auto mb-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <p class="text-gray-600 text-lg sm:text-xl mb-4">No team members found.</p>
            <p class="text-gray-500 text-sm">Team members will appear here once they are added.</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

