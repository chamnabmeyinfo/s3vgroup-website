<?php

declare(strict_types=1);

use App\Database\Migration;

return new class ('20241124_design_options') extends Migration {
    public function up(PDO $pdo): void
    {
        foreach ($this->statements() as $statement) {
            $pdo->exec($statement);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DELETE FROM site_options WHERE key_name LIKE "design_%"');
    }

    /**
     * @return string[]
     */
    private function statements(): array
    {
        return [
            <<<'SQL'
INSERT INTO site_options (id, key_name, value, type, group_name, label, description, priority) VALUES
('opt_200', 'design_font_family', 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif', 'text', 'design', 'Font Family', 'Main font family for the website', 100),
('opt_201', 'design_font_size_base', '16', 'number', 'design', 'Base Font Size (px)', 'Base font size in pixels', 95),
('opt_202', 'design_font_weight_normal', '400', 'number', 'design', 'Normal Font Weight', 'Font weight for normal text (100-900)', 90),
('opt_203', 'design_font_weight_bold', '700', 'number', 'design', 'Bold Font Weight', 'Font weight for bold text (100-900)', 85),
('opt_204', 'design_line_height', '1.6', 'number', 'design', 'Line Height', 'Line height multiplier', 80),
('opt_205', 'design_heading_font', '', 'text', 'design', 'Heading Font', 'Separate font family for headings (optional)', 75),
('opt_206', 'design_border_radius', '8', 'number', 'design', 'Border Radius (px)', 'Default border radius in pixels', 70),
('opt_207', 'design_button_style', 'rounded', 'text', 'design', 'Button Style', 'Button style: rounded, square, or pill', 65),
('opt_208', 'design_button_padding_x', '24', 'number', 'design', 'Button Padding X (px)', 'Horizontal button padding', 60),
('opt_209', 'design_button_padding_y', '12', 'number', 'design', 'Button Padding Y (px)', 'Vertical button padding', 55),
('opt_210', 'design_card_shadow', 'medium', 'text', 'design', 'Card Shadow', 'Card shadow: none, small, medium, or large', 50),
('opt_211', 'design_spacing_unit', '8', 'number', 'design', 'Spacing Unit (px)', 'Base spacing unit for margins and padding', 45),
('opt_212', 'design_container_width', '1280', 'number', 'design', 'Container Max Width (px)', 'Maximum container width', 40),
('opt_213', 'design_header_height', '64', 'number', 'design', 'Header Height (px)', 'Header height in pixels', 35),
('opt_214', 'design_footer_height', 'auto', 'text', 'design', 'Footer Height', 'Footer height (auto or specific px)', 30),
('opt_215', 'design_link_color', '', 'color', 'design', 'Link Color', 'Color for links (empty for primary color)', 25),
('opt_216', 'design_link_hover_color', '', 'color', 'design', 'Link Hover Color', 'Color for links on hover', 20),
('opt_217', 'design_background_pattern', 'none', 'text', 'design', 'Background Pattern', 'Background pattern: none, dots, grid, or lines', 15),
('opt_218', 'design_background_image', '', 'image', 'design', 'Background Image', 'Optional background image URL', 10),
('opt_219', 'design_background_overlay', '0', 'number', 'design', 'Background Overlay Opacity', 'Background overlay opacity (0-100)', 5),
('opt_220', 'design_custom_css', '', 'textarea', 'design', 'Custom CSS', 'Add custom CSS code', 1)
ON DUPLICATE KEY UPDATE label=label
SQL,
        ];
    }
};

