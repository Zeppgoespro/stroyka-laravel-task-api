<?php

namespace App\Orchid\Screens;

use App\Models\Slider;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Actions\Button;
use Illuminate\Http\Request;

class SliderEditScreen extends Screen
{
    public $slider;

    public function query(Slider $slider): iterable
    {
        return [
            'slider' => $slider
        ];
    }

    public function name(): ?string
    {
        return $this->slider->exists ? 'Edit Slider' : 'Create Slider';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Save')
                ->icon('check')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Picture::make('slider.image_path')
                    ->title('Slider Image')
                    ->required()
                    ->storage('public'), // Ensure public storage
                Input::make('slider.title')
                    ->title('Title')
                    ->placeholder('Enter a title'),
                Input::make('slider.description')
                    ->title('Description')
                    ->placeholder('Enter a description'),
            ]),
        ];
    }

    public function save(Slider $slider, Request $request)
    {
        $slider->fill($request->get('slider'));

        if ($request->file('slider.image_path')) {
            $slider->image_path = $request->file('slider.image_path')->store('sliders', 'public');
        }

        $slider->save();

        return redirect()->route('platform.slider.list');
    }
}
