<?php
$activePage = 'settings';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

// Get current loading style preference
$stmt = $db->prepare('SELECT loading_style FROM user_preferences WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$currentStyle = $stmt->fetchColumn() ?: 'variant-1';

ob_start();
?>
<div class="loading-settings-container">
    <div class="loading-settings-panel">
        <div class="loading-settings-header">
            <button class="back-btn" onclick="window.location.href='settings.php'">
                <i class="fas fa-arrow-left"></i>
            </button>
            <h2>Loading Style Preferences</h2>
        </div>
        
        <p class="loading-settings-description">Choose your preferred loading animation style. Click on any style to preview and select it.</p>
        
        <div class="bg-color-selector">
            <label for="previewBgColor">
                <i class="fas fa-palette"></i> Preview Background Color:
            </label>
            <input type="color" id="previewBgColor" value="#87CEEB">
        </div>
        
        <div class="loading-styles-grid">
            <!-- Variant 1: Modern Glassmorphism -->
            <div class="loading-style-card <?= $currentStyle === 'variant-1' ? 'active' : '' ?>" data-variant="variant-1">
                <div class="style-preview-container">
                    <!-- Live Preview Area -->
                    <div class="live-preview-area loading-variant-1">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Modern Glassmorphism</h3>
                    <p>Clean and elegant with glass effect</p>
                    <button class="apply-style-btn" data-variant="variant-1">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 2: Dark Neon -->
            <div class="loading-style-card <?= $currentStyle === 'variant-2' ? 'active' : '' ?>" data-variant="variant-2">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-2">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Dark Neon</h3>
                    <p>Futuristic with neon glow effects</p>
                    <button class="apply-style-btn" data-variant="variant-2">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 3: Warm Gradient -->
            <div class="loading-style-card <?= $currentStyle === 'variant-3' ? 'active' : '' ?>" data-variant="variant-3">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-3">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Warm Gradient</h3>
                    <p>Vibrant orange and red tones</p>
                    <button class="apply-style-btn" data-variant="variant-3">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 4: Nature Wood -->
            <div class="loading-style-card <?= $currentStyle === 'variant-4' ? 'active' : '' ?>" data-variant="variant-4">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-4">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Nature Wood</h3>
                    <p>Warm wooden farm aesthetic</p>
                    <button class="apply-style-btn" data-variant="variant-4">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 5: Minimal Clean -->
            <div class="loading-style-card <?= $currentStyle === 'variant-5' ? 'active' : '' ?>" data-variant="variant-5">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-5">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Minimal Clean</h3>
                    <p>Simple and professional</p>
                    <button class="apply-style-btn" data-variant="variant-5">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 6: Purple Galaxy -->
            <div class="loading-style-card <?= $currentStyle === 'variant-6' ? 'active' : '' ?>" data-variant="variant-6">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-6">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Purple Galaxy</h3>
                    <p>Cosmic purple and pink vibes</p>
                    <button class="apply-style-btn" data-variant="variant-6">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 7: Ocean Blue -->
            <div class="loading-style-card <?= $currentStyle === 'variant-7' ? 'active' : '' ?>" data-variant="variant-7">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-7">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Ocean Blue</h3>
                    <p>Deep ocean waves effect</p>
                    <button class="apply-style-btn" data-variant="variant-7">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 8: Sunset Glow -->
            <div class="loading-style-card <?= $currentStyle === 'variant-8' ? 'active' : '' ?>" data-variant="variant-8">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-8">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Sunset Glow</h3>
                    <p>Warm sunset gradient</p>
                    <button class="apply-style-btn" data-variant="variant-8">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 9: Matrix Green -->
            <div class="loading-style-card <?= $currentStyle === 'variant-9' ? 'active' : '' ?>" data-variant="variant-9">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-9">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Matrix Green</h3>
                    <p>Hacker-style digital rain</p>
                    <button class="apply-style-btn" data-variant="variant-9">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 10: Rose Gold -->
            <div class="loading-style-card <?= $currentStyle === 'variant-10' ? 'active' : '' ?>" data-variant="variant-10">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-10">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Rose Gold</h3>
                    <p>Elegant rose and gold tones</p>
                    <button class="apply-style-btn" data-variant="variant-10">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 11: Arctic Ice -->
            <div class="loading-style-card <?= $currentStyle === 'variant-11' ? 'active' : '' ?>" data-variant="variant-11">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-11">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Arctic Ice</h3>
                    <p>Cool ice blue and white</p>
                    <button class="apply-style-btn" data-variant="variant-11">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 12: Lava Flow -->
            <div class="loading-style-card <?= $currentStyle === 'variant-12' ? 'active' : '' ?>" data-variant="variant-12">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-12">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Lava Flow</h3>
                    <p>Hot lava and ember effects</p>
                    <button class="apply-style-btn" data-variant="variant-12">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 13: Midnight Purple -->
            <div class="loading-style-card <?= $currentStyle === 'variant-13' ? 'active' : '' ?>" data-variant="variant-13">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-13">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Midnight Purple</h3>
                    <p>Deep purple night sky</p>
                    <button class="apply-style-btn" data-variant="variant-13">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 14: Golden Hour -->
            <div class="loading-style-card <?= $currentStyle === 'variant-14' ? 'active' : '' ?>" data-variant="variant-14">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-14">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Golden Hour</h3>
                    <p>Warm golden sunlight glow</p>
                    <button class="apply-style-btn" data-variant="variant-14">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 15: Emerald Forest -->
            <div class="loading-style-card <?= $currentStyle === 'variant-15' ? 'active' : '' ?>" data-variant="variant-15">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-15">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Emerald Forest</h3>
                    <p>Rich emerald green nature</p>
                    <button class="apply-style-btn" data-variant="variant-15">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 16: Vaporwave -->
            <div class="loading-style-card <?= $currentStyle === 'variant-16' ? 'active' : '' ?>" data-variant="variant-16">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-16">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Vaporwave</h3>
                    <p>Retro 80s aesthetic vibes</p>
                    <button class="apply-style-btn" data-variant="variant-16">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 17: Cherry Blossom -->
            <div class="loading-style-card <?= $currentStyle === 'variant-17' ? 'active' : '' ?>" data-variant="variant-17">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-17">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Cherry Blossom</h3>
                    <p>Soft pink sakura petals</p>
                    <button class="apply-style-btn" data-variant="variant-17">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 18: Carbon Fiber -->
            <div class="loading-style-card <?= $currentStyle === 'variant-18' ? 'active' : '' ?>" data-variant="variant-18">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-18">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Carbon Fiber</h3>
                    <p>Dark sleek metal texture</p>
                    <button class="apply-style-btn" data-variant="variant-18">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 19: Rainbow Pride -->
            <div class="loading-style-card <?= $currentStyle === 'variant-19' ? 'active' : '' ?>" data-variant="variant-19">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-19">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Rainbow Pride</h3>
                    <p>Vibrant multi-color spectrum</p>
                    <button class="apply-style-btn" data-variant="variant-19">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 20: Northern Lights -->
            <div class="loading-style-card <?= $currentStyle === 'variant-20' ? 'active' : '' ?>" data-variant="variant-20">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-20">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Northern Lights</h3>
                    <p>Aurora borealis glow</p>
                    <button class="apply-style-btn" data-variant="variant-20">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 21: Cyberpunk -->
            <div class="loading-style-card <?= $currentStyle === 'variant-21' ? 'active' : '' ?>" data-variant="variant-21">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-21">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Cyberpunk</h3>
                    <p>Neon city night vibes</p>
                    <button class="apply-style-btn" data-variant="variant-21">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 22: Mint Fresh -->
            <div class="loading-style-card <?= $currentStyle === 'variant-22' ? 'active' : '' ?>" data-variant="variant-22">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-22">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Mint Fresh</h3>
                    <p>Cool minty turquoise</p>
                    <button class="apply-style-btn" data-variant="variant-22">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 23: Blood Moon -->
            <div class="loading-style-card <?= $currentStyle === 'variant-23' ? 'active' : '' ?>" data-variant="variant-23">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-23">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Blood Moon</h3>
                    <p>Dark crimson red eclipse</p>
                    <button class="apply-style-btn" data-variant="variant-23">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 24: Tropical Paradise -->
            <div class="loading-style-card <?= $currentStyle === 'variant-24' ? 'active' : '' ?>" data-variant="variant-24">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-24">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Tropical Paradise</h3>
                    <p>Bright summer beach vibes</p>
                    <button class="apply-style-btn" data-variant="variant-24">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>

            <!-- Variant 25: Monochrome -->
            <div class="loading-style-card <?= $currentStyle === 'variant-25' ? 'active' : '' ?>" data-variant="variant-25">
                <div class="style-preview-container">
                    <div class="live-preview-area loading-variant-25">
                        <div class="nav-overlay-demo">
                            <div class="nav-particles"></div>
                            <div class="nav-card-demo">
                                <div class="nav-icon-demo">
                                    <div class="nav-icon-inner-demo">
                                        <i class="fas fa-seedling"></i>
                                    </div>
                                </div>
                                <div class="nav-text-demo">
                                    <span class="nav-main-text">Loading</span>
                                    <span class="nav-dots">...</span>
                                </div>
                                <div class="nav-progress-container-demo">
                                    <div class="nav-progress-demo">
                                        <div class="nav-progress-bar-demo">
                                            <div class="nav-progress-glow"></div>
                                            <span class="nav-percent-demo">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="style-info">
                    <h3>Monochrome</h3>
                    <p>Classic black and white</p>
                    <button class="apply-style-btn" data-variant="variant-25">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span class="active-badge"><i class="fas fa-star"></i> Active</span>
                </div>
            </div>
        </div>

        <div class="loading-settings-actions">
            <button class="save-style-btn" id="saveStyleBtn">
                <i class="fas fa-save"></i> Save Selection
            </button>
        </div>

        <div id="styleMessage" class="style-message"></div>
    </div>
</div>
<?php
$content = ob_get_clean();

$pageCss = 'assets_css/loading-settings.css';
$extraCss = ['assets_css/nav-transition.css', 'assets_css/loading-variants.css', 'assets_css/loading-variants-extra.css'];
$extraJs = '<script src="assets_js/loading-settings.js"></script>';

include 'template.php';
?>

