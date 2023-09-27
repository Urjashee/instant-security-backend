<?php

namespace App\Jobs;

use App\Common\ImageResize;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UploadImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $profile_image;
    private $extension;
    private $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($profile_image,$extension,$user)
    {
        $this->profile_image = $profile_image;
        $this->extension = $extension;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        for ($im= 1; $im <= 4; $im++) {
            $normal = Image::make($this->profile_image)->resize(ImageResize::sizeFormat($im),
                ImageResize::sizeFormat($im))->encode($this->extension);
            $imageFileName = ImageResize::sizeFormat($im) . 'x' . ImageResize::sizeFormat($im) .
                '.' . $this->profile_image->getClientOriginalExtension();
            $filePath = 'profile_image/' . $this->user . '/' . $imageFileName;
            $s3 = Storage::disk('public');
            $s3->put($filePath, $normal->stream());
        }
    }
}
