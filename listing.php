<?php
require_once 'includes/db.php';
require_once 'includes/auth_helpers.php';

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT l.*, c.name as category_name, u.name as seller_name, u.surname as seller_surname, u.profile_picture as seller_profile_picture, u.phone as seller_phone, u.email as seller_email 
                       FROM listings l 
                       JOIN categories c ON l.category_id = c.id 
                       JOIN users u ON l.user_id = u.id 
                       WHERE l.id = ?");
$stmt->execute([$id]);
$listing = $stmt->fetch();

if (!$listing) {
    header("Location: browse.php");
    exit;
}

$stmt_img = $pdo->prepare("SELECT image_path FROM listing_images WHERE listing_id = ?");
$stmt_img->execute([$id]);
$images = $stmt_img->fetchAll();

include 'includes/header.php';
?>

<div class="container mx-auto px-6 py-12">
    <div class="flex flex-col lg:flex-row gap-12">
        <div class="lg:w-2/3">
            <div class="glass-panel rounded-[var(--radius)] overflow-hidden mb-8 border border-[var(--border-color)]">
                <?php if(count($images) > 0): ?>
                    
                    <div class="relative w-full h-[50vh] min-h-[400px] bg-black">
                        <img src="assets/uploads/<?php echo htmlspecialchars($images[0]['image_path']); ?>" class="w-full h-full object-contain" alt="Main Image">
                    </div>
                    <?php if(count($images) > 1): ?>
                        <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-2 p-2 bg-[var(--c1)] border-t border-[var(--border-color)]">
                            <?php foreach($images as $img): ?>
                                <div class="aspect-square bg-[var(--c3)] rounded overflow-hidden border border-[var(--border-color)]">
                                    <img src="assets/uploads/<?php echo htmlspecialchars($img['image_path']); ?>" class="w-full h-full object-cover" alt="Thumbnail">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="w-full h-[50vh] min-h-[400px] bg-[var(--c3)] flex items-center justify-center text-[var(--c2)] font-am text-sm uppercase tracking-widest">
                        <span>No Images</span>
                    </div>
                <?php endif; ?>
                
                <div class="p-8 lg:p-12">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 gap-4 border-b border-[var(--border-color)] pb-6">
                        <h2 class="font-ds text-5xl md:text-6xl uppercase tracking-normal leading-none m-0 text-white">
                            <?php echo htmlspecialchars($listing['title']); ?>
                        </h2>
                        <h3 class="font-am text-2xl md:text-3xl text-[var(--primary-color)] m-0 shrink-0">
                            R <?php echo number_format($listing['price'], 2); ?>
                        </h3>
                    </div>
                    
                    <?php if($listing['status'] !== 'active'): ?>
                        <div class="bg-yellow-900/30 border border-yellow-700/50 text-yellow-200 px-4 py-3 rounded mb-8 font-cgr">
                            This listing is currently <?php echo htmlspecialchars($listing['status']); ?>.
                        </div>
                    <?php endif; ?>

                    <div class="flex flex-wrap gap-3 mb-10 font-am text-xs uppercase tracking-wider">
                        <span class="px-3 py-1 bg-white text-black rounded-full"><?php echo htmlspecialchars($listing['category_name']); ?></span>
                        <span class="px-3 py-1 bg-[var(--c3)] border border-[var(--border-color)] text-white rounded-full">Condition: <?php echo ucfirst(htmlspecialchars($listing['condition_state'])); ?></span>
                        <?php 
                        $location_str = [];
                        if(!empty($listing['suburb'])) $location_str[] = $listing['suburb'];
                        $location_str[] = $listing['city'];
                        $location_str[] = $listing['province'];
                        ?>
                        <span class="px-3 py-1 bg-[var(--c3)] border border-[var(--border-color)] text-[var(--c2)] rounded-full">Location: <?php echo htmlspecialchars(implode(', ', $location_str)); ?></span>
                    </div>
                    
                    <h5 class="font-dh text-xl uppercase tracking-normal mb-4 text-[var(--c2)]">Description</h5>
                    <div class="font-cgr text-white text-lg leading-relaxed whitespace-pre-wrap opacity-90"><?php echo htmlspecialchars($listing['description']); ?></div>
                    
                    <div class="mt-12 pt-6 border-t border-[var(--border-color)] font-am text-xs text-[var(--c2)] uppercase tracking-widest">
                        Posted on <?php echo date('F j, Y', strtotime($listing['created_at'])); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="lg:w-1/3 space-y-8">
            <div class="glass-panel p-8 rounded-[var(--radius)]">
                <h5 class="font-dh text-2xl uppercase tracking-normal mb-6">Seller Info</h5>
                <div class="flex items-center gap-4 mb-8">
                    <?php if (!empty($listing['seller_profile_picture'])): ?>
                        <img src="assets/uploads/profiles/<?php echo htmlspecialchars($listing['seller_profile_picture']); ?>" class="w-16 h-16 rounded-full object-cover border-2 border-[var(--primary-color)]">
                    <?php else: ?>
                        <div class="w-16 h-16 rounded-full bg-[var(--c3)] border-2 border-[var(--primary-color)] flex items-center justify-center text-xl font-bold text-white">
                            <?php echo strtoupper(substr($listing['seller_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <p class="font-cgr text-xl font-bold text-white"><?php echo htmlspecialchars($listing['seller_name'] . ' ' . $listing['seller_surname']); ?></p>
                </div>
                
                <?php if(isLoggedIn()): ?>
                    <div class="space-y-4">
                        <?php if($listing['seller_phone']): ?>
                            <a href="tel:<?php echo htmlspecialchars($listing['seller_phone']); ?>" class="block w-full text-center border border-[var(--primary-color)] text-[var(--primary-color)] font-cgr font-bold uppercase tracking-wider rounded-[var(--radius)] px-4 py-4 hover:bg-[var(--primary-color)] hover:text-black transition-colors">
                                Call: <?php echo htmlspecialchars($listing['seller_phone']); ?>
                            </a>
                        <?php endif; ?>
                        <a href="mailto:<?php echo htmlspecialchars($listing['seller_email']); ?>" class="block w-full text-center bg-white text-black font-cgr font-bold uppercase tracking-wider rounded-[var(--radius)] px-4 py-4 hover:bg-gray-200 transition-colors">
                            Email Seller
                        </a>
                    </div>
                <?php else: ?>
                    <div class="bg-[var(--c1)] border border-[var(--border-color)] p-4 rounded text-[var(--c2)] font-cgr text-center">
                        <a href="login.php" class="text-white underline hover:text-[var(--primary-color)] transition-colors">Login</a> to view seller contact details.
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if(isLoggedIn() && ($_SESSION['user_id'] == $listing['user_id'] || isAdmin())): ?>
                <div class="glass-panel border-yellow-600/30 p-8 rounded-[var(--radius)]">
                    <h5 class="font-dh text-xl text-yellow-500 uppercase tracking-normal mb-6">Management</h5>
                    <a href="my_listings.php" class="block w-full text-center border border-yellow-600/50 text-yellow-500 font-cgr font-bold uppercase tracking-wider rounded-[var(--radius)] px-4 py-3 hover:bg-yellow-600/20 transition-colors">
                        Manage Listing
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
