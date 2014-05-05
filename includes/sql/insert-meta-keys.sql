INSERT 
	INTO 
		wp_postmeta ( post_id, meta_key, meta_value )
	SELECT
		post.ID AS post_id,
		'_pronamic_rating_value' AS meta_key,
		'' AS meta_value
	FROM
		wp_posts AS post
			LEFT JOIN
		wp_postmeta AS meta
				ON post.ID = meta.post_id
	WHERE
		post_type = 'wkd_mortician'
			AND
		ID NOT IN (
			SELECT post_id FROM wp_postmeta WHERE meta_key = '_pronamic_rating_value'
		)
	GROUP BY
		post.ID
	;

INSERT 
	INTO 
		wp_postmeta ( post_id, meta_key, meta_value )
	SELECT
		post.ID AS post_id,
		'_pronamic_rating_count' AS meta_key,
		'' AS meta_value
	FROM
		wp_posts AS post
			LEFT JOIN
		wp_postmeta AS meta
				ON post.ID = meta.post_id
	WHERE
		post_type = 'wkd_mortician'
			AND
		ID NOT IN (
			SELECT post_id FROM wp_postmeta WHERE meta_key = '_pronamic_rating_count'
		)
	GROUP BY
		post.ID
	;
