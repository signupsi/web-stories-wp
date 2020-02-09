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
import PropTypes from 'prop-types';
import { DndProvider } from 'react-dnd';
import TouchBackend from 'react-dnd-touch-backend';
import MouseBackend from 'react-dnd-html5-backend';

const hasNativeElementsFromPoint =
  document && (document.elementsFromPoint || document.msElementsFromPoint);

function getDropTargetElementsAtPoint(x, y, dropTargets) {
  return dropTargets.filter((t) => {
    const rect = t.getBoundingClientRect();
    return (
      x >= rect.left && x <= rect.right && y <= rect.bottom && y >= rect.top
    );
  });
}

function hasTouch() {
  try {
    document.createEvent('TouchEvent');
    return true;
  } catch (e) {
    return false;
  }
}

export function WithDnd({ children }) {
  return (
    <DndProvider
      backend={hasTouch() ? TouchBackend : MouseBackend}
      options={{
        enableMouseEvents: true,
        enableKeyboardEvents: true,
        getDropTargetElementsAtPoint:
          !hasNativeElementsFromPoint && getDropTargetElementsAtPoint,
      }}
    >
      {children}
    </DndProvider>
  );
}

WithDnd.propTypes = {
  children: PropTypes.oneOfType([
    PropTypes.arrayOf(PropTypes.node),
    PropTypes.node,
  ]).isRequired,
};
