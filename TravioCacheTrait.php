<?php namespace Model\Travio;

trait TravioCacheTrait
{
	private function checkTravioPhotoCache(string $url): string
	{
		if (str_starts_with($url, 'https://storage.travio.it/'))
			return $this->getPhotoFromCache($url);
		else
			return $url;
	}

	private function getPhotoFromCache(string $url): string
	{
		$path = $this->convertUrlToCachePath($url);

		if (!file_exists(INCLUDE_PATH . $path)) {
			$dir = pathinfo(INCLUDE_PATH . $path, PATHINFO_DIRNAME);
			if (!is_dir($dir))
				mkdir($dir, 0777, true);

			file_put_contents($path, file_get_contents($url));
		}

		return PATH . $path;
	}

	private function invalidatePhotoCache(string $url)
	{
		if (str_starts_with($url, 'https://storage.travio.it/')) {
			$path = $this->convertUrlToCachePath($url);
			if ($path and file_exists(INCLUDE_PATH . $path))
				unlink($path);
		}
	}

	private function convertUrlToCachePath(string $url): string
	{
		return 'app-data/travio/cache/' . substr($url, 26);
	}
}
