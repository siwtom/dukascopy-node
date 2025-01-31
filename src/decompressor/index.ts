import { HistoryConfig } from './../index';

var lzmajs = require('lzma-purejs');
const struct = require('python-struct');

type StructFormat = '>3i2f' | '>5i1f';

function getStructFormat(timeframe: HistoryConfig['timeframe']): StructFormat {
  return timeframe === 'tick' ? '>3i2f' : '>5i1f';
}

type DecompressInput = {
  buffer: Buffer;
  timeframe: HistoryConfig['timeframe'];
};

function decompress(input: DecompressInput): number[][] {
  const { buffer, timeframe } = input;

  if (buffer.length === 0) {
    return [];
  }
  const result: number[][] = [];
  const format = getStructFormat(timeframe);
  const decompressedBuffer = lzmajs.decompressFile(buffer) as Buffer;

  const step = struct.sizeOf(format);

  for (let i = 0, n = decompressedBuffer.length; i < n; i += step) {
    const chunk = decompressedBuffer.slice(i, i + step);
    const unpacked = struct.unpack(format, chunk);

    result.push(unpacked);
  }

  return result;
}

export { decompress };
