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
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useCanvas } from '../../components/canvas';
import useDoubleClick from '../../utils/useDoubleClick';
import { elementFillContent } from '../shared';
import StoryPropTypes from '../../types';

const Element = styled.div`
  ${elementFillContent}
`;

function ImageFrame({ element: { id } }) {
  const {
    actions: { setEditingElement },
  } = useCanvas();
  const handleSingleClick = useCallback(() => {}, []);
  const handleDoubleClick = useCallback(() => setEditingElement(id), [
    id,
    setEditingElement,
  ]);
  const getHandleClick = useDoubleClick(handleSingleClick, handleDoubleClick);
  return <Element onClick={getHandleClick(id)} />;
}

ImageFrame.propTypes = {
  element: StoryPropTypes.elements.image.isRequired,
};

export default ImageFrame;
