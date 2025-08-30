<?php

namespace App\Filament\Resources\Anaks\Schemas;

use Dom\Text;
use Carbon\Carbon;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class AnakForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Anak')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Anak')
                            ->columnSpanFull()
                            ->required()
                            ->maxLength(150),

                        Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->required()
                            ->options([
                                'Laki-Laki' => 'Laki-Laki',
                                'Perempuan' => 'Perempuan',
                            ]),

                        DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->required()
                            ->reactive() // <— penting: trigger hitung ulang di repeater saat tanggal lahir berubah
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                foreach (($get('dataAntropometries') ?? []) as $i => $item) {
                                    $set("dataAntropometries.$i.umur_bulan", age_months_rounded($state, $item['tanggal_ukur'] ?? null));
                                }
                            }),

                        TextInput::make('nama_ayah')
                            ->label('Nama Ayah')
                            ->required()
                            ->maxLength(150),

                        TextInput::make('nama_ibu')
                            ->label('Nama Ibu')
                            ->required()
                            ->maxLength(150),

                        Textarea::make('alamat')
                            ->label('Alamat Rumah')
                            ->required()
                            ->columnSpanFull()
                            ->rows('4'),
                    ]),

                Section::make('Data Ukur Anak')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('Data Antropometry')
                            ->columns(2)
                            ->addActionLabel('Tambah Data Ukur')
                            ->addActionAlignment(Alignment::Start)
                            ->label('Data Ukur')
                            ->relationship('dataAntropometries')
                            ->schema([
                                DatePicker::make('tanggal_ukur')
                                    ->label('Tanggal Ukur')
                                    ->required()
                                    ->reactive() // <— penting: hitung umur_bulan saat tanggal_ukur berubah
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $set('umur_bulan', age_months_rounded($get('../../tanggal_lahir'), $state));
                                    }),

                                TextInput::make('umur_bulan')
                                    ->label('Umur Bulan (0-60)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(60)
                                    ->required()
                                    ->disabled()      // <— dikunci agar diisi otomatis
                                    ->dehydrated(),   // <— tetap disimpan ke database

                                Select::make('tipe_ukur')
                                    ->label('Tipe Ukur Tinggi/Panjang Badan')
                                    ->required()
                                    ->options([
                                        'berdiri' => 'Berdiri',
                                        'telentang' => 'Telentang',
                                    ]),

                                TextInput::make('tinggi')
                                    ->label('Tinggi/Panjang Badan')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(200)
                                    ->step('0.1')
                                    ->suffix('cm')
                                    ->required(),

                                TextInput::make('berat')
                                    ->label('Berat Badan')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(200)
                                    ->step('0.1')
                                    ->suffix('kg')
                                    ->required(),

                                TextInput::make('lingkar_lengan_atas')
                                    ->label('Diameter Lingkar Lengan Atas')
                                    ->numeric()
                                    ->step('0.1')
                                    ->suffix('cm')
                                    ->required(),

                                TextInput::make('lingkar_kepala')
                                    ->label('Diameter Lingkar Kepala')
                                    ->numeric()
                                    ->step('0.1')
                                    ->suffix('cm')
                                    ->required(),

                                Repeater::make('prediksi')
                                    ->relationship('prediksi')
                                    ->addable(false)
                                    ->deletable(true)
                                    ->defaultItems(1)
                                    ->minItems(1)
                                    ->maxItems(1)
                                    ->collapsible()
                                    ->columnSpanFull()
                                    ->columns(3)
                                    ->label('Hasil Prediksi')
                                    ->schema([
                                        TextInput::make('status_tbu')
                                            ->label('Status Tinggi Badan / Umur'),

                                        TextInput::make('status_bbu')
                                            ->label('Status Berat Badan / Umur'),

                                        TextInput::make('status_tbbb')
                                            ->label('Status Tinggi Badan / Berat Badan'),

                                        Textarea::make('rekomendasi')
                                            ->label('Hasil Rekomendasi')
                                            ->columnSpanFull(),

                                    ])
                            ])
                    ]),

            ]);
    }
}
