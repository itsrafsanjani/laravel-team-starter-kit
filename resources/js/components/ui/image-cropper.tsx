import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Slider } from '@/components/ui/slider';
import { Cropper } from '@origin-space/image-cropper';
import { useCallback, useEffect, useState } from 'react';

interface ImageCropperProps {
  isOpen: boolean;
  onClose: () => void;
  onCrop: (croppedImageBlob: Blob) => void;
  imageFile: File | null;
  aspectRatio?: number;
  minWidth?: number;
  minHeight?: number;
}

type Area = { x: number; y: number; width: number; height: number };

export default function ImageCropper({
  isOpen,
  onClose,
  onCrop,
  imageFile,
  aspectRatio = 1,
  minWidth = 100,
  minHeight = 100,
}: ImageCropperProps) {
  const [cropData, setCropData] = useState<Area | null>(null);
  const [imageUrl, setImageUrl] = useState<string>('');
  const [zoom, setZoom] = useState(1);

  // Create object URL when image file changes
  useEffect(() => {
    if (imageFile) {
      const url = URL.createObjectURL(imageFile);
      setImageUrl(url);
      return () => URL.revokeObjectURL(url);
    }
  }, [imageFile]);

  // Generate cropped image blob
  const generateCroppedImage = useCallback(async (area: Area) => {
    if (!imageFile) return null;

    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    if (!ctx) return null;

    // Create a new image to get the original dimensions
    const img = new Image();
    img.crossOrigin = 'anonymous';

    return new Promise<Blob | null>((resolve) => {
      img.onload = () => {
        // Set canvas size to the crop area size
        canvas.width = area.width;
        canvas.height = area.height;

        // Draw the cropped portion
        ctx.drawImage(
          img,
          area.x,
          area.y,
          area.width,
          area.height,
          0,
          0,
          area.width,
          area.height
        );

        canvas.toBlob((blob) => {
          resolve(blob);
        }, 'image/jpeg', 0.9);
      };

      img.src = imageUrl;
    });
  }, [imageFile, imageUrl]);

  const handleCrop = useCallback(async () => {
    if (!cropData) return;

    try {
      const croppedBlob = await generateCroppedImage(cropData);
      if (croppedBlob) {
        onCrop(croppedBlob);
        onClose();
      }
    } catch (error) {
      console.error('Error cropping image:', error);
    }
  }, [cropData, generateCroppedImage, onCrop, onClose]);

  const handleClose = () => {
    setCropData(null);
    setZoom(1);
    onClose();
  };

  if (!imageFile || !imageUrl) {
    return null;
  }

  return (
    <Dialog open={isOpen} onOpenChange={handleClose}>
      <DialogContent className="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Crop Image</DialogTitle>
          <DialogDescription>
            Adjust the crop area to select the portion of the image you want to use.
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-4">
          {/* Image Cropper */}
          <div className="flex justify-center">
            <Cropper.Root
              image={imageUrl}
              aspectRatio={aspectRatio}
              zoom={zoom}
              onCropChange={setCropData}
              onZoomChange={setZoom}
              className="relative flex h-80 w-full cursor-move touch-none items-center justify-center overflow-hidden rounded-md border focus:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            >
              {/* Required for accessibility */}
              <Cropper.Description className="sr-only">
                Use mouse or touch to pan and zoom the image. Use the crop handles to adjust the selection area.
              </Cropper.Description>
              <Cropper.Image className="pointer-events-none h-full w-full select-none object-cover" />
              <Cropper.CropArea className="pointer-events-none absolute border-2 border-dashed border-background shadow-[0_0_0_9999px_rgba(0,0,0,0.6)]" />
            </Cropper.Root>
          </div>

          {/* Zoom Controls */}
          <div className="space-y-2">
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium">Zoom</span>
              <span className="text-sm text-gray-500">{Math.round(zoom * 100)}%</span>
            </div>
            <div className="flex items-center space-x-4">
              <span className="text-sm text-gray-500">-</span>
              <Slider
                value={[zoom]}
                onValueChange={(value) => setZoom(value[0])}
                min={0.5}
                max={3}
                step={0.1}
                className="flex-1"
              />
              <span className="text-sm text-gray-500">+</span>
            </div>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" onClick={handleClose}>
            Cancel
          </Button>
          <Button onClick={handleCrop} disabled={!cropData}>
            Crop & Use
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
