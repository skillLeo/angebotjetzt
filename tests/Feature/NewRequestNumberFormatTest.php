<?php

use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

afterEach(fn () => Carbon::setTestNow());

it('generates the new AJYYMMXXXX format for a fresh month with no existing requests', function () {
    Carbon::setTestNow(Carbon::create(2026, 8, 15));

    $number = ServiceRequest::nextRequestNumber();

    expect($number)->toMatch('/^AJ2608\d{4}$/');
});

it('never continues an old-format AJ-YYYY-NNNNNN number, even if one exists', function () {
    Carbon::setTestNow(Carbon::create(2026, 8, 15));
    $type = checklistServiceType();
    ServiceRequest::create([
        'request_number' => 'AJ-2026-000842', 'service_type_id' => $type->id,
        'vehicle_make' => 'VW', 'vehicle_model' => 'Golf', 'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => 'x', 'contact_email' => 'x1@example.de', 'contact_phone' => '+49123', 'status' => 'open',
    ]);

    $number = ServiceRequest::nextRequestNumber();

    expect($number)->toMatch('/^AJ2608\d{4}$/')
        ->and((int) substr($number, -4))->toBeLessThanOrEqual(10); // fresh random 3-10 start, not derived from the old number's "000842"
});

it('increments within the same month via the same random +3..+10 jump used before', function () {
    Carbon::setTestNow(Carbon::create(2026, 8, 15));
    $type = checklistServiceType();
    $first = ServiceRequest::createWithUniqueNumber([
        'service_type_id' => $type->id, 'vehicle_make' => 'VW', 'vehicle_model' => 'Golf',
        'plz' => '50667', 'ort' => 'Köln', 'contact_name' => 'x', 'contact_email' => 'x2@example.de',
        'contact_phone' => '+49123', 'status' => 'open',
    ]);
    $second = ServiceRequest::createWithUniqueNumber([
        'service_type_id' => $type->id, 'vehicle_make' => 'VW', 'vehicle_model' => 'Golf',
        'plz' => '50667', 'ort' => 'Köln', 'contact_name' => 'x', 'contact_email' => 'x3@example.de',
        'contact_phone' => '+49123', 'status' => 'open',
    ]);

    $firstSeq = (int) substr($first->request_number, -4);
    $secondSeq = (int) substr($second->request_number, -4);

    expect($first->request_number)->toMatch('/^AJ2608\d{4}$/')
        ->and($second->request_number)->toMatch('/^AJ2608\d{4}$/')
        ->and($secondSeq - $firstSeq)->toBeGreaterThanOrEqual(3)->toBeLessThanOrEqual(10);
});

it('resets to a fresh small random sequence at the start of a new month, uniquely, with no collision against last month', function () {
    $type = checklistServiceType();

    // A busy end-of-July with a high sequence number.
    Carbon::setTestNow(Carbon::create(2026, 7, 31));
    $julyLast = null;
    for ($i = 0; $i < 5; $i++) {
        $julyLast = ServiceRequest::createWithUniqueNumber([
            'service_type_id' => $type->id, 'vehicle_make' => 'VW', 'vehicle_model' => 'Golf',
            'plz' => '50667', 'ort' => 'Köln', 'contact_name' => 'x', 'contact_email' => "july{$i}@example.de",
            'contact_phone' => '+49123', 'status' => 'open',
        ]);
    }
    $julySeq = (int) substr($julyLast->request_number, -4);
    expect($julyLast->request_number)->toStartWith('AJ2607');
    expect($julySeq)->toBeGreaterThanOrEqual(15); // 5 rows of +3..+10 jumps from a first value of >=3

    // The very first request of August: must not collide with, or continue from, July's sequence.
    Carbon::setTestNow(Carbon::create(2026, 8, 1));
    $augFirst = ServiceRequest::createWithUniqueNumber([
        'service_type_id' => $type->id, 'vehicle_make' => 'VW', 'vehicle_model' => 'Golf',
        'plz' => '50667', 'ort' => 'Köln', 'contact_name' => 'x', 'contact_email' => 'aug1@example.de',
        'contact_phone' => '+49123', 'status' => 'open',
    ]);
    $augSeq = (int) substr($augFirst->request_number, -4);

    expect($augFirst->request_number)->toStartWith('AJ2608')
        ->and($augSeq)->toBeLessThanOrEqual(10) // fresh random 3-10 start, independent of July's much higher count
        ->and(ServiceRequest::where('request_number', $augFirst->request_number)->count())->toBe(1);
});

it('keeps existing old-format records completely untouched', function () {
    $type = checklistServiceType();
    $old = ServiceRequest::create([
        'request_number' => 'AJ-2026-000842', 'service_type_id' => $type->id,
        'vehicle_make' => 'VW', 'vehicle_model' => 'Golf', 'plz' => '50667', 'ort' => 'Köln',
        'contact_name' => 'x', 'contact_email' => 'old@example.de', 'contact_phone' => '+49123', 'status' => 'open',
    ]);

    expect($old->fresh()->request_number)->toBe('AJ-2026-000842');
});
