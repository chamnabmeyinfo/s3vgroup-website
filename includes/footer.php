    </main>

    <footer class="bg-[#0b3a63] text-white mt-20">
        <div class="container mx-auto px-4 py-12">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <span class="text-xl font-bold">S3V Forklift</span>
                    </div>
                    <p class="text-gray-300 text-sm">
                        Professional forklift solutions in Cambodia. Sales, rental, and service.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li><a href="/products.php" class="hover:text-white transition-colors">Browse Forklifts</a></li>
                        <li><a href="/quote.php" class="hover:text-white transition-colors">Request Quote</a></li>
                        <li><a href="/contact.php" class="hover:text-white transition-colors">Contact Us</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold mb-4">Contact</h3>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <?php echo htmlspecialchars($siteConfig['contact']['phone']); ?>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <?php echo htmlspecialchars($siteConfig['contact']['email']); ?>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <?php echo htmlspecialchars($siteConfig['contact']['address']); ?>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold mb-4">Business Hours</h3>
                    <p class="text-sm text-gray-300"><?php echo htmlspecialchars($siteConfig['contact']['hours']); ?></p>
                </div>
            </div>

            <div class="border-t border-white/20 mt-8 pt-8 text-center text-sm text-gray-300">
                <p>&copy; <?php echo date('Y'); ?> S3V Forklift Solutions. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            // Add mobile menu functionality if needed
            console.log('Mobile menu toggle');
        }
    </script>
</body>
</html>
