DELETE FROM 
	wp_postmeta
WHERE
	meta_key = '_pronamic_rating_value'
		AND
	meta_value = ''
;

DELETE FROM 
	wp_postmeta
WHERE
	meta_key = '_pronamic_rating_value'
		AND
	meta_value = '0'
;

DELETE FROM 
	wp_postmeta
WHERE
	meta_key = '_pronamic_rating_count'
		AND
	meta_value = ''
;

DELETE FROM 
	wp_postmeta
WHERE
	meta_key = '_pronamic_rating_count'
		AND
	meta_value = '0'
;