<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Seed the feature registry. Statuses reflect what the platform can actually
     * deliver today: live, simulated (demo/pilot data), beta, coming_soon, disabled.
     */
    public function run(): void
    {
        $features = [
            ['code' => 'live_dashboard', 'name' => 'Live dashboard', 'category' => 'platform', 'status' => 'live', 'sort' => 10, 'description' => 'Real-time view of enrolled devices and activity.'],
            ['code' => 'call_log', 'name' => 'Call log', 'category' => 'activity', 'status' => 'live', 'sort' => 20, 'description' => 'Incoming, outgoing and missed calls reported by the agent.'],
            ['code' => 'text_messages', 'name' => 'Text messages', 'category' => 'activity', 'status' => 'live', 'sort' => 30, 'description' => 'SMS and in-app message activity reported by the agent.'],
            ['code' => 'gps_location', 'name' => 'GPS location logs', 'category' => 'safety', 'status' => 'live', 'sort' => 40, 'description' => 'Location history reported by enrolled devices.'],
            ['code' => 'geofence_alerts', 'name' => 'Geofence alerts', 'category' => 'safety', 'status' => 'live', 'sort' => 50, 'description' => 'Safe-zone rules with distance-based alerts.'],
            ['code' => 'alerts', 'name' => 'Keyword & geofence alerts', 'category' => 'safety', 'status' => 'live', 'sort' => 60, 'description' => 'Rules that raise alerts you review and acknowledge.'],
            ['code' => 'whatsapp', 'name' => 'WhatsApp activity', 'category' => 'social', 'status' => 'beta', 'sort' => 70, 'description' => 'WhatsApp message activity on enrolled devices.'],
            ['code' => 'email', 'name' => 'Email activity', 'category' => 'activity', 'status' => 'simulated', 'sort' => 80, 'description' => 'Overview of sent and received email.'],
            ['code' => 'browser_history', 'name' => 'Browser history', 'category' => 'activity', 'status' => 'simulated', 'sort' => 90, 'description' => 'Visited-site overview to help keep browsing safe.'],
            ['code' => 'photo_log', 'name' => 'Photo log', 'category' => 'activity', 'status' => 'simulated', 'sort' => 100, 'description' => 'Photos stored on enrolled devices.'],
            ['code' => 'video_log', 'name' => 'Videos log', 'category' => 'activity', 'status' => 'simulated', 'sort' => 110, 'description' => 'Videos stored on enrolled devices.'],
            ['code' => 'contacts', 'name' => 'Contact list', 'category' => 'activity', 'status' => 'simulated', 'sort' => 120, 'description' => 'Contacts saved on enrolled devices.'],
            ['code' => 'diagnostics', 'name' => 'Device diagnostics', 'category' => 'device', 'status' => 'simulated', 'sort' => 130, 'description' => 'Battery, storage and device health status.'],
            ['code' => 'location_sharing', 'name' => 'Family & partner location sharing', 'category' => 'safety', 'status' => 'live', 'sort' => 140, 'description' => 'Mutual, consent-based location sharing.'],
            ['code' => 'remote_lock', 'name' => 'Lock a lost device', 'category' => 'device', 'status' => 'simulated', 'sort' => 150, 'description' => 'Remotely lock a lost or stolen enrolled device.'],
            ['code' => 'notes', 'name' => 'Notes overview', 'category' => 'activity', 'status' => 'simulated', 'sort' => 160, 'description' => 'Notes stored on enrolled devices.'],
            ['code' => 'calendar', 'name' => 'Calendar overview', 'category' => 'activity', 'status' => 'simulated', 'sort' => 170, 'description' => 'Calendar entries on enrolled devices.'],
            ['code' => 'facebook', 'name' => 'Facebook activity', 'category' => 'social', 'status' => 'coming_soon', 'sort' => 180, 'description' => 'Summaries of Facebook use.'],
            ['code' => 'skype', 'name' => 'Skype activity', 'category' => 'social', 'status' => 'coming_soon', 'sort' => 190, 'description' => 'Summaries of Skype use.'],
            ['code' => 'instagram', 'name' => 'Instagram activity', 'category' => 'social', 'status' => 'coming_soon', 'sort' => 200, 'description' => 'Summaries of Instagram use.'],
            ['code' => 'wechat', 'name' => 'WeChat activity', 'category' => 'social', 'status' => 'coming_soon', 'sort' => 210, 'description' => 'Summaries of WeChat use.'],
            ['code' => 'line', 'name' => 'Line activity', 'category' => 'social', 'status' => 'coming_soon', 'sort' => 220, 'description' => 'Summaries of Line use.'],
            ['code' => 'x_twitter', 'name' => 'X (Twitter) activity', 'category' => 'social', 'status' => 'coming_soon', 'sort' => 230, 'description' => 'Summaries of X use.'],
            ['code' => 'audit_trail', 'name' => 'Admin audit trail', 'category' => 'platform', 'status' => 'live', 'is_public' => false, 'sort' => 300, 'description' => 'Immutable log of administrative actions.'],
            ['code' => 'simulation_engine', 'name' => 'Simulation engine', 'category' => 'platform', 'status' => 'coming_soon', 'is_public' => false, 'sort' => 310, 'description' => 'Generates realistic pilot activity for demo devices.'],
        ];

        foreach ($features as $feature) {
            Feature::updateOrCreate(
                ['code' => $feature['code']],
                array_merge(['is_public' => true], $feature),
            );
        }
    }
}
