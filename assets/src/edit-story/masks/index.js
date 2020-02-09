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
import { useDrop } from 'react-dnd';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { useState } from 'react';
import StoryPropTypes from '../types';

// Important! This file cannot use `styled-components` or any stateful/context
// React features to stay compatible with the "output" templates.

export const MaskTypes = {
  HEART: 'heart',
  STAR: 'star',
  RECTANGLE: 'rectangle',
  TRIANGLE: 'triangle',
  ROUNDED: 'rounded-rectangle',
  POLYGON: 'polygon',
};

export const MASKS = {
  [MaskTypes.HEART]: {
    name: __('Heart', 'web-stories'),
    path:
      'M 0.5,1 C 0.5,1,0,0.7,0,0.3 A 0.25,0.25,1,1,1,0.5,0.3 A 0.25,0.25,1,1,1,1,0.3 C 1,0.7,0.5,1,0.5,1 Z',
  },
  [MaskTypes.STAR]: {
    name: __('Star', 'web-stories'),
    path:
      'M 0.5 0.75 L 0.207031 0.90625 L 0.261719 0.578125 L 0.0234375 0.34375 L 0.351562 0.296875 L 0.5 0 L 0.648438 0.296875 L 0.976562 0.34375 L 0.738281 0.578125 L 0.792969 0.90625 Z M 0.5 0.75 ',
  },
  [MaskTypes.RECTANGLE]: {
    name: __('Rectangle', 'web-stories'),
    path: 'M 0,0 1,0 1,1 0,1 0,0',
  },
  [MaskTypes.TRIANGLE]: {
    name: __('Triangle', 'web-stories'),
    path: 'M 0.5 0 L 1 1 L 0 1 Z M 0.5 0',
  },
  [MaskTypes.CIRCLE]: {
    name: __('Circle', 'web-stories'),
    path:
      'M 0.5 0 C 0.777344 0 1 0.222656 1 0.5 C 1 0.777344 0.777344 1 0.5 1 C 0.222656 1 0 0.777344 0 0.5 C 0 0.222656 0.222656 0 0.5 0 Z M 0.5 0 ',
  },
  [MaskTypes.POLYGON]: {
    name: __('Polygon', 'web-stories'),
    path:
      'M 0.5 0 L 0.976562 0.34375 L 0.792969 0.90625 L 0.207031 0.90625 L 0.0234375 0.34375 Z M 0.5 0',
  },
};

const FILL_STYLE = {
  position: 'absolute',
  top: 0,
  left: 0,
  right: 0,
  bottom: 0,
};

const dropTargetStyles = {
  transition: 'opacity 0.5s',
};

export function WithElementMask({ element, fill, style, children, ...rest }) {
  const mask = getElementMaskProperties(element);
  return (
    <WithtMask
      fill={fill}
      style={style}
      mask={mask}
      elementId={element.id}
      {...rest}
    >
      {children}
    </WithtMask>
  );
}

WithElementMask.propTypes = {
  element: StoryPropTypes.element.isRequired,
  style: PropTypes.object,
  fill: PropTypes.bool,
  children: StoryPropTypes.children.isRequired,
};

function WithtMask({ elementId, mask, fill, style, children, ...rest }) {
  const maskType = (mask && mask.type) || null;

  const fillStyle = fill ? FILL_STYLE : null;

  const allStyles = {
    ...fillStyle,
    ...style,
  };

  const [{ isOver, isOverCurrent }, drop] = useDrop({
    accept: 'media',
    drop(item, monitor) {
      const didDrop = monitor.didDrop();
      if (didDrop) {
      }
      // setHasDropped(true)
      // setHasDroppedOnChild(didDrop)
    },
    collect: (monitor) => ({
      isOver: monitor.isOver(),
      isOverCurrent: monitor.isOver({ shallow: true }),
    }),
  });

  if (maskType && MASKS[maskType]) {
    // @todo: Chrome cannot do inline clip-path using data: URLs.
    // See https://bugs.chromium.org/p/chromium/issues/detail?id=1041024.

    const maskId = `mask-${maskType}-${elementId}`;
    allStyles.clipPath = `url(#${maskId})`;

    return (
      <div
        style={allStyles}
        {...rest}
        // onMouseEnter={() => setHover(true)}
        // onMouseLeave={() => setHover(false)}
        onMouseMove={() => {
          // let root = document.getElementsByTagName('svg')[0];
          // let rpos = root.createSVGRect();
          // rpos.x = evt.clientX;
          // rpos.y = evt.clientY;
          // rpos.width = rpos.height = 1;
          // let list = root.getIntersectionList(rpos, null);
          // for (let i = 0; i < list.length; i++) {
          //   if (list[i] != evt.target) {
          //     console.log('intersects', list.length, evt.target);
          //     // $(list[i]).mousemove();
          //   }
          // }
        }}
      >
        <svg width={0} height={0}>
          <defs>
            <clipPath id={maskId} clipPathUnits="objectBoundingBox">
              <path d={MASKS[maskType].path} />
            </clipPath>
          </defs>
        </svg>
        {children}
        <svg
          viewBox="0 0 1 1"
          width="100%"
          height="100%"
          preserveAspectRatio="none"
          onMouseEnter={() => console.log('intersecting')}
        >
          <path
            ref={drop}
            vectorEffect="non-scaling-stroke"
            strokeWidth="30"
            fill="none"
            stroke="#0063F9"
            strokeLinecap="round"
            strokeLinejoin="round"
            d={MASKS[maskType].path}
            style={{ ...dropTargetStyles, opacity: isOver ? 0.3 : 0 }}
          />
        </svg>
      </div>
    );
  }
  return (
    <div style={allStyles} {...rest}>
      {children}
    </div>
  );
}

WithtMask.propTypes = {
  elementId: PropTypes.string.isRequired,
  mask: StoryPropTypes.mask,
  style: PropTypes.object,
  fill: PropTypes.bool,
  children: StoryPropTypes.children.isRequired,
};

function getElementMaskProperties({ type, mask, ...rest }) {
  if (mask) {
    return mask;
  }
  return getDefaultElementMaskProperties({ type, ...rest });
}

function getDefaultElementMaskProperties({}) {
  // @todo: mask-based shapes (square, circle, etc) automatically assume masks.
  return null;
}
