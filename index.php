<?php
require_once 'includes/db.php';
require_once 'includes/auth_helpers.php';

// Fetch the most recent active listings
$stmt = $pdo->query("
    SELECT l.*, c.name as category_name, u.name as seller_name,
    (SELECT image_path FROM listing_images WHERE listing_id = l.id LIMIT 1) as main_image 
    FROM listings l 
    JOIN categories c ON l.category_id = c.id 
    JOIN users u ON l.user_id = u.id
    WHERE l.status = 'active'
    ORDER BY l.created_at DESC
    LIMIT 6
");
$recent_listings = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="page-lock flex flex-col items-center justify-center min-h-[60vh] text-center border-b border-[var(--border-color)] pb-12 mb-16 px-4">
    <div class="mb-8 relative flex flex-col items-center justify-center">
        <img src="public/logo.png" alt="WavOnline Logo" class="h-24 md:h-32 lg:h-48 object-contain relative z-10 -mb-8 md:-mb-12 lg:-mb-16 drop-shadow-[0_10px_15px_rgba(0,0,0,0.8)]">
        <h1 class="font-ds text-6xl md:text-8xl lg:text-6xl uppercase tracking-normal leading-none relative z-0 mt-4 md:mt-0">
            Wav<span class="text-[var(--primary-color)]">Online</span>
        </h1>
    </div>
    <p class="font-cgr text-[var(--c2)] text-lg md:text-2xl max-w-2xl mb-10 opacity-80">
        South Africa's premier customer-to-customer marketplace. Buy/sell goods directly with other users in your community.
    </p>
    <div class="flex flex-col sm:flex-row gap-4">
        <a href="browse.php" class="px-8 py-4 bg-white text-black font-cgr font-bold uppercase tracking-wider rounded-[var(--radius)] hover:bg-gray-200 transition-colors">
            Browse Listings
        </a>
        <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="px-8 py-4 glass-panel text-white font-cgr font-bold uppercase tracking-wider rounded-[var(--radius)] hover:bg-[var(--c3)] transition-colors">
                Create Account
            </a>
        <?php else: ?>
            <a href="create_listing.php" class="px-8 py-4 glass-panel text-white font-cgr font-bold uppercase tracking-wider rounded-[var(--radius)] hover:bg-[var(--c3)] transition-colors">
                Sell an Item
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="container mx-auto px-6 mb-16">
    <div class="flex items-end justify-between mb-8 border-b border-[var(--border-color)] pb-4">
        <h2 class="font-dh text-4xl md:text-5xl uppercase tracking-normal m-0">Recently Added</h2>
        <a href="browse.php" class="font-am text-sm text-[var(--c2)] hover:text-white uppercase hidden sm:block border-b border-transparent hover:border-white transition-colors">View All</a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach($recent_listings as $listing): ?>
            <a href="listing.php?id=<?php echo $listing['id']; ?>" class="block group">
                <div class="listing-card flex flex-col h-full bg-[var(--c1)]">
                    <div class="relative h-64 overflow-hidden border-b border-[var(--border-color)]">
                        <?php if($listing['main_image']): ?>
                            <img src="assets/uploads/<?php echo htmlspecialchars($listing['main_image']); ?>" alt="Listing Image" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        <?php else: ?>
                            <div class="w-full h-full bg-[var(--c3)] flex items-center justify-center text-[var(--c2)] font-am text-xs uppercase tracking-widest">
                                <span>No Image</span>
                            </div>
                        <?php endif; ?>
                        <div class="absolute top-4 right-4 bg-black/50 backdrop-blur px-3 py-1 rounded-full border border-white/10 font-am text-xs text-white uppercase tracking-wider">
                            R <?php echo number_format($listing['price'], 2); ?>
                        </div>
                    </div>
                    
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h5 class="font-dh text-2xl uppercase tracking-normal mb-2 group-hover:text-[var(--primary-color)] transition-colors">
                                <?php echo htmlspecialchars($listing['title']); ?>
                            </h5>
                            <div class="flex items-center gap-2 font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-1">
                                <span>[<?php echo htmlspecialchars($listing['category_name']); ?>]</span>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-4 border-t border-[var(--border-color)] flex justify-between items-center font-cgr text-sm text-[var(--c2)]">
                            <span class="opacity-70">By <?php echo htmlspecialchars($listing['seller_name']); ?></span>
                            <span class="opacity-70"><i class="bi bi-geo-alt hidden"></i><?php echo htmlspecialchars($listing['city'] . ', ' . $listing['province']); ?></span>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
        
        <?php if(empty($recent_listings)): ?>
            <div class="col-span-full">
                <div class="glass-panel p-12 text-center text-[var(--c2)] font-cgr rounded-[var(--radius)]">
                    No listings available yet. Be the first to sell something!
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if(!empty($recent_listings)): ?>
        <div class="text-center mt-12 sm:hidden">
            <a href="browse.php" class="inline-block px-6 py-3 glass-panel font-am text-sm uppercase tracking-wider rounded-[var(--radius)] hover:bg-[var(--c3)] transition-colors">View All Listings</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>