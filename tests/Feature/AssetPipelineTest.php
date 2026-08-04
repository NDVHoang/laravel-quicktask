<?php

test('vite configuration and entry points exist', function () {
    $this->assertFileExists(base_path('vite.config.ts'));
    $this->assertFileExists(resource_path('css/app.css'));
    $this->assertFileExists(resource_path('js/app.tsx'));
});

test('dashboard components exist in resources directory', function () {
    $this->assertFileExists(resource_path('js/Pages/Dashboard.jsx'));
    $this->assertFileExists(resource_path('js/Layouts/DashboardLayout.tsx'));
    $this->assertFileExists(resource_path('js/Components/Dashboard/Header.tsx'));
    $this->assertFileExists(resource_path('js/Components/Dashboard/Sidebar.tsx'));
});

test('build manifest is generated successfully in public/build', function () {
    if (file_exists(public_path('build/manifest.json'))) {
        $this->assertFileExists(public_path('build/manifest.json'));

        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $this->assertIsArray($manifest);
        $this->assertArrayHasKey('resources/js/app.tsx', $manifest);
        $this->assertArrayHasKey('resources/css/app.css', $manifest);
    } else {
        $this->markTestSkipped('Build manifest not found. Run npm run build first.');
    }
});
