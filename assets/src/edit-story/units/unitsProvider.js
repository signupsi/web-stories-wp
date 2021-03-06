/*
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * WordPress dependencies
 */
import { useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import StoryPropTypes from '../types';
import Context from './context';
import {
  dataToEditorX,
  dataToEditorY,
  editorToDataX,
  editorToDataY,
  getBox,
} from './dimensions';

function UnitsProvider({ pageSize, children }) {
  const { width: pageWidth, height: pageHeight } = pageSize;
  const state = useMemo(
    () => ({
      state: {
        pageSize: { width: pageWidth, height: pageHeight },
      },
      actions: {
        dataToEditorX: (x) => dataToEditorX(x, pageWidth),
        dataToEditorY: (y) => dataToEditorY(y, pageHeight),
        editorToDataX: (x) => editorToDataX(x, pageWidth),
        editorToDataY: (y) => editorToDataY(y, pageHeight),
        getBox: (element) => getBox(element, pageWidth, pageHeight),
      },
    }),
    [pageWidth, pageHeight]
  );

  return <Context.Provider value={state}>{children}</Context.Provider>;
}

UnitsProvider.propTypes = {
  pageSize: StoryPropTypes.size.isRequired,
  children: StoryPropTypes.children.isRequired,
};

export default UnitsProvider;
