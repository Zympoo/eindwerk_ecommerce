<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                
                // SECTION 1: Base Product Data
                Section::make('Product Information')
                    ->schema([
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required(),
                            
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, string $operation, $set) => 
                                $operation === 'create' || 'edit' ? $set('slug', Str::slug($state)) : null
                            ),
                            
                        TextInput::make('slug')
                            ->id('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                            
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->inputMode('decimal')
                            ->prefix('€')
                            ->formatStateUsing(fn ($state) => $state !== null ? number_format($state / 100, 2, '.', '') : '0.00')
                            ->dehydrateStateUsing(fn ($state) => $state !== null ? (int) round(floatval(str_replace(',', '.', $state)) * 100) : null),
                            
                        Toggle::make('is_active')
                            ->label('Visible in webshop')
                            ->default(true),
                            
                        FileUpload::make('image_path')
                            ->image()
                            ->directory('products')
                            ->columnSpanFull(),
                            
                        Textarea::make('description')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // SECTION 2: Product Variants (Repeater)
                Section::make('Product Variants')
                    ->description('Manage different sizes, colors, or editions for this product.')
                    ->schema([
                        Repeater::make('variants')
                            ->relationship('variants') // Refers to the variants() relation in your Product model
                            ->schema([
                                TextInput::make('name')
                                    ->label('Variant Name')
                                    ->placeholder('e.g., Size M / Blue')
                                    ->required(),
                                    
                                TextInput::make('additional_price')
                                    ->label('Additional Price')
                                    ->numeric()
                                    ->prefix('€')
                                    ->default(0)
                                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state / 100, 2, '.', '') : '0.00')
                                    ->dehydrateStateUsing(fn ($state) => $state !== null ? (int) round(floatval(str_replace(',', '.', $state)) * 100) : 0),
                                    
                                TextInput::make('stock')
                                    ->label('Stock')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                                    
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                            ])
                            ->columns(4)
                            ->defaultItems(0)
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->extraAttributes([
                                'style' => 'max-height: 400px; overflow-y: auto; padding: 0 8px;'
                            ])
                    ]),
            ])
            ->columns(1);
    }
}