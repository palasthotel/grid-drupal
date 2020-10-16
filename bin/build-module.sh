#!/bin/sh

MODULE_SLUG="grid"
PROJECT_PATH=$(pwd)
BUILD_PATH="${PROJECT_PATH}/build"
DEST_PATH="$BUILD_PATH/$MODULE_SLUG"

echo "Generating build directory..."
rm -rf "$BUILD_PATH"
mkdir -p "$DEST_PATH"

echo "Syncing files..."
rsync -rL --exclude=bin --exclude=_grid --exclude=lib/grid/node_modules  $PROJECT_PATH/. "$DEST_PATH/"

echo "Cleanup files..."
rm "$DEST_PATH/.gitignore"
rm "$DEST_PATH/.gitmodules"
rm -r "$DEST_PATH/build"
rm "$DEST_PATH/lib/grid/.gitignore"
rm "$DEST_PATH/lib/grid/Butlerfile"
rm "$DEST_PATH/lib/grid/composer.json"
rm "$DEST_PATH/lib/grid/package.json"
rm "$DEST_PATH/lib/grid/package-lock.json"
if [ -d "$DEST_PATH/lib/grid/node_modules" ]; then
  rm -r "$DEST_PATH/lib/grid/node_modules"
fi
rm $DEST_PATH/lib/grid/webpack*
rm -r "$DEST_PATH/lib/grid/src"
rm -r "$DEST_PATH/lib/grid/scss"

echo "Generating zip file..."
cd "$BUILD_PATH" || exit
zip -q -r "${MODULE_SLUG}.zip" "$MODULE_SLUG/"

cd "$PROJECT_PATH" || exit
mv "$BUILD_PATH/${MODULE_SLUG}.zip" "$PROJECT_PATH"
echo "${MODULE_SLUG}.zip file generated!"

echo "Cleanup build path..."
rm -rf "$BUILD_PATH"

echo "Build done!"
