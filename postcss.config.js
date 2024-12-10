module.exports = {
	loader: "postcss-loader",
	options: {
		postcssOptions: {
			plugins: [
				require("autoprefixer"),
				require("css-mqpacker"),
				require("cssnano")({
					preset: [
						"default",
						{
							discardComments: {
								removeAll: true,
							},
						},
					],
				}),
				[
					"postcss-preset-env",
					{
						// Options
					},
				],
			],
		},
	},
};
