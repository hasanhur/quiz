<?php

namespace App\Service;

// Not working

class SlugGenerator
{
    /**
     * @param integer
     * @return integer
     */
    public function getUniqueSlug($slugNo) {
        $min = 0;
        $max = $slugNo;
        $lastMax = $slugNo;
        while ($min != $max) {
            $mid = ($max - $min + 1) / 2;
            if (!$this->getDoctrine()->getRepository(Subjects::class)->findBy(['slug' => $slug . '-' . $mid])) {
                $lastMax = $max;
                $max = $mid - 1;
            } else {
                $min = $mid + 1;
            }
        }
        return $lastMax;
    }
}
