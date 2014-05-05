UPDATE 
	wp_postmeta
SET
	meta_value = 0
WHERE
	meta_key = '_pronamic_rating_value'
		AND
	meta_value = ''
;

UPDATE 
	wp_postmeta
SET
	meta_value = 0
WHERE
	meta_key = '_pronamic_rating_count'
		AND
	meta_value = ''
;
