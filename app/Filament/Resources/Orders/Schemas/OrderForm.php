<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Data')
                    ->schema([
                        TextInput::make('order_number')
                            ->label('Order Number')
                            ->disabled(),
                        
                        TextInput::make('total_price')
                            ->label('Total Price')
                            ->disabled()
                            ->prefix('€'),
                        
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending'),
                    ])
                    ->columns(3),

                Section::make('Ordered Products')
                    ->schema([
                        Repeater::make('items')
                            ->label('Items')
                            ->relationship('items')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name')
                                    ->disabled(),
                                
                                Select::make('product_variant_id')
                                    ->label('Variant')
                                    ->relationship('variant', 'name')
                                    ->disabled(),
                                
                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->disabled(),
                                
                                TextInput::make('price')
                                    ->label('Price')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled(),
                            ])
                            ->columns(2)
                            ->extraAttributes([
                                'style' => 'max-height: 400px; overflow-y: auto; padding: 0 8px;'
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),

                Section::make('Shipping Address')
                    ->schema([
                        TextInput::make('shipping_address.street')
                            ->label('Street + House Number'),
                        
                        TextInput::make('shipping_address.city')
                            ->label('City'),
                        
                        TextInput::make('shipping_address.postal_code')
                            ->label('Postal Code'),
                        
                        TextInput::make('shipping_address.country')
                            ->label('Country'),
                    ])
                    ->columns(2),

                Section::make('Stripe Data')
                    ->schema([
                        TextInput::make('stripe_payment_intent_id')
                            ->label('Stripe Payment Intent ID')
                            ->disabled(),
                        
                        TextInput::make('stripe_session_id')
                            ->label('Stripe Session ID')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ])
            ->columns(1);
    }
}